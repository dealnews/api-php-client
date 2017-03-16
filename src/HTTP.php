<?php

namespace DealNews\API\Client;

/**
 * Class HTTP
 *
 * Handles HTTP client access to the DealNews API
 *
 * @package     DealNews\API\Client
 * @author      Jeremy Earle <jearle@dealnews.com>
 * @copyright   1997-Present dealnews.com, Inc.
 * @license     https://opensource.org/licenses/BSD-3-Clause
 */
class HTTP {

    /**
     * API Client Auth
     *
     * @var \DealNews\API\Client\Auth
     */
    protected $auth;

    /**
     * GuzzleHTTP Client
     *
     * @var \GuzzleHTTP\Client
     */
    protected $client;

    /**
     * List of formats that API endpoints can potentially return
     *
     * @var array
     */
    protected $accepted_formats = [
        'json' => 'application/json',
        'xml' => 'text/xml,application/xml',
        'rss' => 'application/rss+xml',
    ];

    /**
     * When set to true, we will request the api with the "no-cache" header
     *
     * @var bool
     */
    public $nocache = false;

    /**
     * The default return format for all requests. You can override this on a per-request basis
     *
     * @var string
     */
    public $default_format = "json";

    /**
     * @param   string  $public_key     A key provided by DealNews for access to public endpoints
     * @param   string  $secret_key     A key provided by DealNews for access to private endpoints
     * @param   string  $host           A protocol and host address to access the DealNews API
     * @param   object  $client         For testing purposes, only
     */
    public function __construct($public_key, $secret_key="", $host="https://api.dealnews.com", $client=null) {
        $this->auth = new Auth($public_key, $secret_key);

        if (empty($client)) {
            $this->client = new \GuzzleHttp\Client([
                'base_uri' => $host,
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => 'DealNewsPHPAPIClient/0.1',
                ]
            ]);
        } else {
            $this->client = $client;
        }
    }

    /**
     * Performs a GET request against an API endpoint
     *
     * @param   string  $path           The url path (not including protocol or not. Example: "/features")
     * @param   array   $query_params   Optional set of query string params (Example: ['start' => 30])
     * @param   string  $format         Optional format of return data (defaults to $default_format)
     *
     * @return  array                   An array containing the HTTP response status code, response headers, and the response body
     */
    public function get ($path, $query_params=[], $format=null) {
        return $this->makeRequest("GET", $path, $query_params, $format);
    }


    /**
     * Performs a POST request against an API endpoint
     *
     * @param   string  $path           The url path (not including protocol or not. Example: "/login")
     * @param   array   $post_data      Optional set of form post data to send (Example: ['username' => 'johndoe'])
     * @param   string  $format         Optional format of return data (defaults to $default_format)
     *
     * @return  array                   An array containing the HTTP response status code, response headers, and the response body
     */
    public function post ($path, $post_data=[], $format=null) {
        return $this->makeRequest("POST", $path, $post_data, $format);
    }



    protected function makeRequest ($method, $path, $data=[], $format=null) {
        $options = [
            'headers' => $this->buildRequestHeaders($format, $path, "GET"),
        ];


        $data_option_key = "query";
        if ($method == "POST") {
            $data_option_key = "form_params";
        }
        $options[$data_option_key] = $data;

        $response = $this->client->request($method, $path, $options);

        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $response->getBody(),
        ];
    }


    protected function buildRequestHeaders ($format, $path, $method) {
        $headers = [
            'Accept-Encoding' => 'gzip,deflate',
        ];

        if (!empty($format) && !empty($this->accepted_formats[strtolower($format)])) {
            $headers['Accept'] = $this->accepted_formats[strtolower($format)];
        } elseif (!empty($this->accepted_formats[strtolower($this->default_format)])) {
            $headers['Accept'] = $this->accepted_formats[strtolower($this->default_format)];
        }

        $x_dn_date = date('r');
        $headers['Authorization'] = $this->auth->getAuth($path, $method, $x_dn_date);
        $headers['x-dn-date'] = $x_dn_date;

        if (!empty($this->nocache)) {
            $headers['Cache-Control'] = "no-cache";
        }

        return $headers;
    }
}
