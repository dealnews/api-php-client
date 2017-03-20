<?php

namespace DealNews\API\Client\Tests;

use DealNews\API\Client\Auth;

class AuthTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test standard authorization needed for public endpoints
     */
    public function testPublicAuth () {
        $auth = new Auth ("foo");

        $header = $auth->getAuth("/features", "GET", date('r'));

        $this->assertEquals("DN foo", $header);
    }



    public function testPrivateAuth () {
        $auth = new Auth ("foo", "bar");

        $path = "/features";
        $method = "GET";
        $date = date("r");

        $header = $auth->getAuth($path, $method, $date);

        $hash = hash_hmac("sha1", "$method\n$path\n$date", "bar");

        $this->assertEquals("DN foo:" . $hash, $header);
    }
}
