<?php


namespace AppBundle\Service;

use Neoxygen\NeoClient\ClientBuilder;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class BaseManager
 *
 * @package AppBundle\Service
 */
class BaseManager
{
    protected $schema;
    protected $host;
    protected $port;
    protected $username;
    protected $password;

    /**
     * @var \Neoxygen\NeoClient\Client
     */
    protected $neoclient;

    /**
     * Sets NeoClient instance
     *
     * @param string $schema
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     */
    public function setNeoClient($schema, $host, $port, $username, $password)
    {
        $this->schema = $schema;
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Sends cypher query
     *
     * Wrapper function to the original `sendCypherQuery`, but this one
     * always returns an array and formats the values.
     *
     * @param string $cypherQuery
     * @param array $parameters
     * @return array
     * @throws \Exception
     */
    public function sendCypherQuery($cypherQuery, array $parameters)
    {
        $curl = curl_init('localhost:7474/db/data/transaction/commit');

        curl_setopt($curl, CURLOPT_USERPWD, "neo4j:n43l068s");
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
