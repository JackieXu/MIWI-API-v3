<?php


namespace AppBundle\Service;

/**
 * Class BaseManager
 *
 * @package AppBundle\Service
 */
class BaseManager
{
    /**
     * @var string
     */
    const TRANSACTION_URL = '/db/data/transaction/commit';

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $auth;

    /**
     * @param string $schema
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     */
    public function setNeo4j($schema, $host, $port, $username, $password)
    {
        $this->baseUrl = sprintf('%s://%s:%d', $schema, $host, $port);
        $this->auth = sprintf('%s:%s', $username, $password);
    }

    /**
     * Sends cypher query
     *
     * Uses the Neo4j REST API to send a cypher query and parses its response.
     *
     * @param string $cypherQuery
     * @param array $parameters
     * @return array
     * @throws \Exception
     */
    public function sendCypherQuery($cypherQuery, array $parameters)
    {
        $curl = curl_init($this->baseUrl.self::TRANSACTION_URL);

        curl_setopt($curl, CURLOPT_USERPWD, $this->auth);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json; charset=UTF-8'));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(
            array(
                'statements' => array(
                    array(
                        'statement' => $cypherQuery,
                        'parameters' => $parameters
                    )
                )
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);

        if (json_last_error()) {
            return array();
        }

        if ($data['errors']) {
            throw new \Exception(sprintf(
                    '[%s] %s',
                    $data['errors'][0]['code'],
                    $data['errors'][0]['message']
                )
            );
        }

        $keys = $data['results'][0]['columns'];
        $rows = $data['results'][0]['data'];

        $keyCount = count($keys);
        $rowCount = count($rows);
        $result = array();

        for ($r = 0; $r < $rowCount; $r++) {
            $result[] = array();
            for ($k = 0; $k < $keyCount; $k++) {
                $result[$r][$keys[$k]] = $rows[$r]['row'][$k];
            }
        }

        return $result;
    }
}
