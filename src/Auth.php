<?php

namespace DealNews\API\Client;

/**
 * Class Auth
 *
 * Constructs authorization strings for API client access
 *
 * @package     DealNews\API\Client
 * @author      Jeremy Earle <jearle@dealnews.com>
 * @copyright   1997-Present dealnews.com, Inc.
 * @license     https://opensource.org/licenses/BSD-3-Clause
 */
class Auth {

    /**
     * Public key for API access
     *
     * @var string
     */
    protected $public_key;

    /**
     * Secret key for API access to private endpoints
     *
     * @var string
     */
    protected $secret_key;

    /**
     * @param   string  $public_key
     * @param   string  $secret_key
     */
    public function __construct($public_key, $secret_key="") {
        $this->public_key = $public_key;
        $this->secret_key = $secret_key;
    }


    /**
     * Returns an authorization header value for endpoint access
     *
     * @param   string  $path       The url path (including the starting "/" character)
     * @param   string  $method     The current request method (GET, POST, etc..)
     * @param   string  $x_dn_date  The current date/time in RFC 2822 format
     * @return  string
     */
    public function getAuth ($path, $method, $x_dn_date) {
        $auth = "DN " . $this->public_key;
        if (!empty($this->secret_key)) {
            $auth .= ":" . $this->buildSecretHash($path, $method, $x_dn_date);
        }

        return $auth;
    }


    /**
     * Builds a secret hash based on provided information about the current request
     *
     * @param   string  $path
     * @param   string  $method
     * @param   string  $x_dn_date
     * @return  string
     */
    protected function buildSecretHash ($path, $method, $x_dn_date) {
        return hash_hmac("sha1", "$method\n$path\n$x_dn_date", $this->secret_key);
    }
}
