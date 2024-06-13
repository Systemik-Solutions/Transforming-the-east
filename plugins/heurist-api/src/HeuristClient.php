<?php

namespace HeuristAPI;

class HeuristClient
{

    protected $host;

    protected $dbName;

    /**
     * HeuristClient constructor.
     *
     * @param string $host
     *   The Heurist host name.
     * @param string $dbName
     *   The Heurist DB name.
     */
    public function __construct($host, $dbName)
    {
        $this->host = $host;
        $this->dbName = $dbName;
    }

    /**
     * Send a get request.
     *
     * @param string $path
     *   The request path.
     * @param $data
     *   The request data.
     *
     * @return mixed|null
     *   The response data.
     */
    public function get($path, $data)
    {
        $queryString = $this->createQueryString($data);
        $requestURL = "{$this->host}{$path}?{$queryString}";

        $response = wp_remote_get($requestURL, ['timeout' => 180]);
        $body = wp_remote_retrieve_body($response);
        if (!empty($body)) {
            return json_decode($body, true);
        }
        return null;
    }

    /**
     * Send a GET request and expect an XML response.
     *
     * @param string $path
     *   The request path.
     * @param $data
     *   The request data.
     *
     * @return \SimpleXMLElement|null
     *   The parsed XML, or null on failure.
     */
    public function getXml($path, $data)
    {
        $queryString = $this->createQueryString($data);
        $requestURL = "{$this->host}{$path}?{$queryString}";

        $response = wp_remote_get($requestURL, ['timeout' => 60]);
        $body = wp_remote_retrieve_body($response);

        if (!empty($body)) {
            return new \SimpleXMLElement($body);
        }
        return null;
    }

    /**
     * Create the request query string based on the data.
     *
     * This will additionally add the DB name into the query string.
     *
     * @param array $data
     *   The data used to create the query string.
     *
     * @return string
     */
    protected function createQueryString($data)
    {
        $data['db'] = $this->dbName;
        $parameters = [];
        foreach ($data as $name => $value) {
            $parameters[] = "{$name}={$value}";
        }
        return implode('&', $parameters);
    }
}
