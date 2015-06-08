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
        $this->generateToken(4121);
        $user = $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   u.email = {email}
            RETURN  u.password
        ', array(
            'email' => '(?i)'.$email
        ));

        if ($user) {
            $hashedPassword = $user['password'];

            if (password_verify($password, $hashedPassword)) {
                // create token
                $token = '';

                return $token;
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
     * @return int
     */
    public function register($email, $password, $firstName, $lastName, $birthdate)
    {
        return 1;
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
            return $userId === $values['userId'];
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
    protected function generateToken($userId, $duration = 7200000)
    {
        $length = mt_rand(16, 20);
        $token = bin2hex(openssl_random_pseudo_bytes($length));

        $this->sendCypherQuery('
            MATCH   (u:USER)
            WHERE   id(u) = {userId}
            CREATE  (u)-[ut:HAS]->(t:TOKEN {
                endDate: timestamp() + {duration}
            })
            RETURN  t
        ', array(
            'userId' => $userId,
            'duration' => $duration
        ));

        return $token;
    }
}