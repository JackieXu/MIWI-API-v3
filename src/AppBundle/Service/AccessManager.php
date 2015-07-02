<?php


namespace AppBundle\Service;

/**
 * Class AccessManager
 *
 * @package AppBundle\Service
 */
class AccessManager extends BaseManager
{
    /**
     * Logs user in using given credentials
     *
     * @param string $email
     * @param string $password
     * @return string|bool False if unsuccessful, otherwise an access token
     */
    public function login($email, $password)
    {
        $user = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   u.email = {email}
            RETURN  id(u) as id,
                    u.password as password,
                    u.status as status
        ', array(
            'email' => $email
        ));

        if ($user) {
            $user = $user[0];
            $hashedPassword = $user['password'];

            if (password_verify($password, $hashedPassword)) {
                // create token
                $token = $this->generateToken($user['id']);

                return array(
                    'id' => $user['id'],
                    'accessToken' => $token,
                    'status' => $user['status']
                );
            }

            return false;
        }

        return false;
    }

    /**
     * Logs user in using Google access token
     *
     * Grabs user info from Google servers and attempts to log in using the
     * data from there. If user does not have an an account, a new one will
     * be made using the newly acquired information.

     * @param string $token
     * @return bool|string
     */
    public function loginWithGoogle($token)
    {
        // Verify with Google, return error if not valid
        $client = new \Google_Client();
        $client->setDeveloperKey('AIzaSyCQwE_4Zd2hciHSUATeII4yRMN5zWIjGNk');

        $ticket = $client->verifyIdToken($token);

        if ($ticket) {
            $data = $ticket->getAttributes();

            return $data['payload']['sub'];
        }

        return false;
    }

    /**
     * Registers a new user
     *
     * @param string $email
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @param int $birthdate
     * @param int|null $social
     * @return array|null
     */
    public function register($email, $password, $firstName, $lastName, $birthdate, $social = null)
    {
        $userId = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   u.email = {email}
            RETURN  id(u) as id
        ', array(
            'email' => $email
        ));

        if ($userId) {
            $userId = $userId[0];
        }

        // If an ID was returned, user already exists
        if (array_key_exists('id', $userId) && is_int($userId['id'])) {
            return null;
        } else {
            $password = password_hash($password, PASSWORD_BCRYPT);
            $birthdate = (int) $birthdate;

            $user = $this->sendCypherQuery('
                CREATE  (u:USER {
                    email: {email},
                    password: {password},
                    firstName: {firstName},
                    lastName: {lastName},
                    birthdate: {birthdate},
                    status: 0,
                    social: {social}
                })
                RETURN  id(u) as id
            ', array(
                'email' => $email,
                'password' => $password,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'birthdate' => $birthdate,
                'social' => $social
            ));

            if ($user) {
                $user = $user[0];
            }

            if (array_key_exists('id', $user) && is_int($user['id'])) {
                $token = $this->generateToken($user['id']);

                return array($user['id'], $token, 0);
            }

            return null;
        }
    }

    /**
     * Checks if token has access to user
     *
     * @param string $token
     * @param int $userId
     * @return bool
     */
    public function hasAccessToUser($token, $userId)
    {
        $values = $this->sendCypherQuery('
            MATCH   (u:USER)-[:HAS]->(t:TOKEN)
            WHERE   t.expirationDate > timestamp()
            AND     t.token = {token}
            RETURN  id(u) as userId
        ', array(
            'token' => $token
        ));

        if ($values) {
            return $userId === $values[0]['userId'];
        }

        return false;
    }

    /**
     * Request password change token
     *
     * @param string $email
     * @return bool
     * @throws \Exception
     */
    public function requestPasswordToken($email)
    {
        $token = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   u.email = {email}
            WITH    u
            MERGE   (t:TOKEN {userId: id(u)})
            SET     t.expirationDate = timestamp() + 604800000
            SET     t.code = {code}
            SET     t.email = u.email
            RETURN  t.code as code,
                    t.email as email
        ', array(
            'email' => $email,
            'code' => sha1(md5($email).((string) time()))
        ));

        if (!$token) {
            return false;
        }

        $token = $token[0];

        $mail = new \Swift_Message();
        $mail->setTo($token['email']);
        $mail->setFrom('info@miwi.com');
        $mail->setSubject('MIWI Password Recovery');
        $mail->setBody($this->templateEngine->render(':mails/access:password_token.html.twig', array(
            'code' => $token['code']
        )));

        $this->mailer->send($mail);

        return true;
    }

    /**
     * Change password
     *
     * @param string $tokenCode
     * @param string $password
     * @return bool
     * @throws \Exception
     */
    public function changePassword($tokenCode, $password)
    {
        $token = $this->sendCypherQuery('
            MATCH   (t:TOKEN)
            WHERE   t.code = {token}
            AND     t.expirationDate > timestamp()
            RETURN  t.userId as userId
        ', array(
            'token' => $tokenCode
        ));

        if ($token) {
            $token = $token[0];
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $this->sendCypherQuery('
                MATCH   (u:USER)
                WHERE   id(u) = {userId}
                SET     u.password = {passwordHash}
                RETURN  id(u) as id
            ', array(
                'userId' => $token['userId'],
                'passwordHash' => $passwordHash
            ));

            $this->sendCypherQuery('
                MATCH   (t:TOKEN)
                WHERE   t.code = {token}
                SET     t.expirationDate = 0
                RETURN  id(t)
            ', array(
                'token' => $tokenCode
            ));

            return true;
        }

        return false;
    }

    /**
     * Generates user identification token
     *
     * A user identifier token is generated, the end date is calculated
     * via `$duration`.
     *
     * @param int $userId
     * @param int $duration Duration in milliseconds
     * @return string
     * @throws \Exception
     */
    protected function generateToken($userId, $duration = 6048500000)
    {
        $length = mt_rand(16, 20);
        $tokenCode = bin2hex(openssl_random_pseudo_bytes($length));

        $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            CREATE  (u)-[ut:HAS]->(t:TOKEN {
                expirationDate: timestamp() + {duration},
                token: {tokenCode}
            })
            RETURN  id(t)
        ', array(
            'userId' => $userId,
            'duration' => $duration,
            'tokenCode' => $tokenCode
        ));

        return $tokenCode;
    }
}