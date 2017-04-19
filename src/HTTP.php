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
     * List of request options that we accept and what request method
     * they are supported in.
     *
     * @var array
     */
    protected $valid_request_options = [
        'form_params' => ['POST'],
        'format' => ['GET', 'POST'],
        'headers' => ['GET', 'POST'],
        'query' => ['GET'],
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
     * Describes the SSL certificate verification behavior of a request.
     *
     *      - Set to true to enable SSL certificate verification and use the default CA bundle
     *        provided by operating system.
     *
     *      - Set to false to disable certificate verification (this is insecure!).
     *
     *      - Set to a string to provide the path to a CA bundle to enable verification using
     *        a custom certificate.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     * @var bool|string
     */
    public $verify_ssl = true;

    /**
     * @param   string                          $public_key     A key provided by DealNews for access to public endpoints
     * @param   string                          $secret_key     A key provided by DealNews for access to private endpoints
     * @param   string                          $host           A protocol and host address to access the DealNews API
     * @param   Auth                            $auth           For testing purposes, only
     * @param   \GuzzleHttp\Handler\MockHandler $handler        For testing purposes, only
     */
    public function __construct($public_key, $secret_key="", $host="https://api.dealnews.com", $auth = null, $handler=null) {
        if (empty($auth)) {
            $this->auth = new Auth($public_key, $secret_key);
        } else {
            $this->auth = $auth;
        }

        $guzzle_config = [
            'base_uri' => $host,
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'DealNewsPHPAPIClient/0.1',
            ]
        ];

        if (!empty($handler)) {
            $guzzle_config['handler'] = $handler;
        }

        $this->client = new \GuzzleHttp\Client($guzzle_config);
    }

    /**
     * Performs a GET request against an API endpoint
     *
     * @param   string  $path               The url path (not including protocol or not. Example: "/features")
     * @param   array   $request_options    Options for the request such as headers, query string (query), etc..
     *
     * @return  array                       An array containing the HTTP response status code, response headers, and the response body
     *
     * @throws  Exception\InvalidOption
     * @throws  Exception\InvalidMethodOption
     */
    public function get ($path, $request_options=[]) {
        return $this->makeRequest("GET", $path, $request_options);
    }


    /**
     * Performs a POST request against an API endpoint
     *
     * @param   string  $path               The url path (not including protocol or not. Example: "/login")
     * @param   array   $request_options    Options for the request such as headers, query string (query), etc..
     *
     * @return  array                       An array containing the HTTP response status code, response headers, and the response body
     *
     * @throws  Exception\InvalidOption
     * @throws  Exception\InvalidMethodOption
     */
    public function post ($path, $request_options=[]) {
        return $this->makeRequest("POST", $path, $request_options);
    }


    /**
     * Makes a request (GET or POST) to an api endpoint
     *
     * @param   string  $method             The request method (GET or POST)
     * @param   string  $path               The relative path to the endpoint
     * @param   array   $request_options    A list of request options
     *
     * @return  array                       An array containing the HTTP response status code, response headers, and the response body
     *
     * @throws Exception\InvalidOption
     * @throws Exception\InvalidMethodOption
     */
    protected function makeRequest ($method, $path, $request_options=[]) {
        $this->validateOptions($method, $request_options);

        $options = [
            'headers' => $this->buildRequestHeaders($request_options, $path, "GET"),
        ];

        if (!empty($request_options['headers'])) {
            $options['headers'] = array_merge($options['headers'], $request_options['headers']);
            unset($request_options['headers']);
        }

        if ($this->verify_ssl !== true) {
            $options['verify'] = $this->verify_ssl;
        }

        if (!empty($request_options)) {
            $options = array_merge($options, $request_options);
        }

        $response = $this->client->request($method, $path, $options);

        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $response->getBody(),
        ];
    }


    /**
     * Determines if the provided array contains valid request options
     *
     * @param   string  $method             The request method (GET or POST)
     * @param   array   $options            A list of request options to be verified
     *
     * @throws Exception\InvalidOption
     * @throws Exception\InvalidMethodOption
     */
    protected function validateOptions ($method, $options) {
        foreach ($options as $option => $value) {
            if (!empty($this->valid_request_options[$option])) {
                if (!in_array($method, $this->valid_request_options[$option])) {
                    throw new Exception\InvalidMethodOption("Invalid option $option for method " . $method);
                }
            } else {
                throw new Exception\InvalidOption("Invalid option $option");
            }
        }
    }


    /**
     * Builds the minimum headers necessary to make an API request
     *
     * @param   array   $options    Request options
     * @param   string  $path       A relative path to the API endpoint
     * @param   string  $method     The request method (GET or POST)
     *
     * @return  array               A list of header names and values needed for a request
     */
    protected function buildRequestHeaders ($options, $path, $method) {
        $headers = [
            'Accept-Encoding' => 'gzip,deflate',
        ];

        if (!empty($options['format']) && !empty($this->accepted_formats[strtolower($options['format'])])) {
            $headers['Accept'] = $this->accepted_formats[strtolower($options['format'])];
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
