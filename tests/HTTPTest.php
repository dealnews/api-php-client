<?php

namespace DealNews\API\Client\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;
use DealNews\API\Client\HTTP;

class HTTPTest extends \PHPUnit_Framework_TestCase {

    /**
     * Runs a standard get request and checks for the proper response back
     * from the get method as well as headers, target url parts, etc...
     */
    public function testGet () {

        $public_key = "foo";
        $secret_key = "";

        $response_headers = [
            'Content-Type' => ["application/json", "charset=utf-8"],
        ];

        $response_body = "{'foo': 'bar'}";

        $container = [];
        $responses = [
            new Response(200, $response_headers, $response_body),
        ];

        $handler = $this->getGuzzleHandler($container, $responses);

        $http = new HTTP($public_key, $secret_key, "https://api.dealnews.com", $this->mockAuth($public_key, $secret_key), $handler);
        $client_response = $http->get("/features");

        // test the response
        $this->assertEquals(true, is_array($client_response), "HTTP::get method failed to return an array");
        $this->assertArrayHasKey("status", $client_response);
        $this->assertEquals(200, $client_response['status'], "HTTP::get method should have returned a HTTP status of 200");
        $this->assertArrayHasKey("headers", $client_response);
        $this->assertEquals($response_headers, $client_response['headers'], "HTTP::get method did not return the expected headers");
        $this->assertArrayHasKey("body", $client_response);
        $this->assertEquals($response_body, $client_response['body'], "HTTP::get method did not return the expected body");

        // check the history
        foreach ($container as $history) {
            $this->assertEquals("GET", $history['request']->getMethod(), "HTTP::get method failed to use the GET request method");

            $this->assertEquals(["application/json"], $history['request']->getHeader("Accept"), "HTTP::get method failed to use the proper accept header");

            $this->assertEquals(["DN " . $public_key], $history['request']->getHeader("Authorization"), "HTTP::get method failed to use the proper authorization header");

            $this->assertEquals("https", $history['request']->getUri()->getScheme(), "HTTP::get method failed to use the proper scheme/protocol");

            $this->assertEquals("api.dealnews.com", $history['request']->getUri()->getHost(), "HTTP::get method failed to use the proper host");

            $this->assertEquals("/features", $history['request']->getUri()->getPath(), "HTTP::get method failed to use the proper url path");
        }
    }


    /**
     * Tests that a query params provided to the get method gets turned into a query string
     */
    public function testGetWithQueryString () {
        $public_key = "foo";
        $secret_key = "";

        $response_headers = [
            'Content-Type' => ["application/json", "charset=utf-8"],
        ];

        $response_body = "{'foo': 'bar'}";

        $container = [];
        $responses = [
            new Response(200, $response_headers, $response_body),
        ];

        $handler = $this->getGuzzleHandler($container, $responses);

        $http = new HTTP($public_key, $secret_key, "https://api.dealnews.com", $this->mockAuth($public_key, $secret_key), $handler);
        $client_response = $http->get("/features", ['start' => 30, 'limit' => 10]);

        // check the history
        foreach ($container as $history) {
            $this->assertEquals("start=30&limit=10", $history['request']->getUri()->getQuery(), "HTTP::get method failed to use the proper query string");
        }
    }


    /**
     * Tests that the default response format can be changed
     */
    public function testGetChangedDefaultFormat () {
        $public_key = "foo";
        $secret_key = "";

        $response_headers = [
            'Content-Type' => ["application/json", "charset=utf-8"],
        ];

        $response_body = "{'foo': 'bar'}";

        $container = [];
        $responses = [
            new Response(200, $response_headers, $response_body),
        ];

        $handler = $this->getGuzzleHandler($container, $responses);

        $http = new HTTP($public_key, $secret_key, "https://api.dealnews.com", $this->mockAuth($public_key, $secret_key), $handler);
        $http->default_format = "xml";
        $client_response = $http->get("/features");

        // check the history
        foreach ($container as $history) {
            $this->assertEquals(["text/xml,application/xml"], $history['request']->getHeader("Accept"), "HTTP::get method failed to use the proper accept header");
        }
    }


    /**
     * Tests that the response format can be overridden on a per-request basis
     */
    public function testGetOverrideFormat () {
        $public_key = "foo";
        $secret_key = "";

        $response_headers = [
            'Content-Type' => ["application/json", "charset=utf-8"],
        ];

        $response_body = "{'foo': 'bar'}";

        $container = [];
        $responses = [
            new Response(200, $response_headers, $response_body),
        ];

        $handler = $this->getGuzzleHandler($container, $responses);

        $http = new HTTP($public_key, $secret_key, "https://api.dealnews.com", $this->mockAuth($public_key, $secret_key), $handler);
        $client_response = $http->get("/features", [], "xml");

        // check the history
        foreach ($container as $history) {
            $this->assertEquals(["text/xml,application/xml"], $history['request']->getHeader("Accept"), "HTTP::get method failed to use the proper accept header");
        }
    }


    /**
     * Tests that when the nocache flag is set to true, the proper cache-control header is set
     */
    public function testGetNocache () {
        $public_key = "foo";
        $secret_key = "";

        $response_headers = [
            'Content-Type' => ["application/json", "charset=utf-8"],
        ];

        $response_body = "{'foo': 'bar'}";

        $container = [];
        $responses = [
            new Response(200, $response_headers, $response_body),
        ];

        $handler = $this->getGuzzleHandler($container, $responses);

        $http = new HTTP($public_key, $secret_key, "https://api.dealnews.com", $this->mockAuth($public_key, $secret_key), $handler);
        $http->nocache = true;
        $client_response = $http->get("/features");

        // check the history
        foreach ($container as $history) {
            $this->assertEquals(["no-cache"], $history['request']->getHeader("Cache-Control"), "HTTP::get method failed to use the proper accept header");
        }
    }



    /**
     * Runs a standard post request and checks for the proper response back
     * from the get method as well as headers, target url parts, etc...
     */
    public function testPost () {

        $public_key = "foo";
        $secret_key = "";

        $response_headers = [
            'Content-Type' => ["application/json", "charset=utf-8"],
        ];

        $response_body = "{'foo': 'bar'}";

        $container = [];
        $responses = [
            new Response(200, $response_headers, $response_body),
        ];

        $handler = $this->getGuzzleHandler($container, $responses);

        $http = new HTTP($public_key, $secret_key, "https://api.dealnews.com", $this->mockAuth($public_key, $secret_key), $handler);
        $client_response = $http->post("/features");

        // test the response
        $this->assertEquals(true, is_array($client_response), "HTTP::post method failed to return an array");
        $this->assertArrayHasKey("status", $client_response);
        $this->assertEquals(200, $client_response['status'], "HTTP::post method should have returned a HTTP status of 200");
        $this->assertArrayHasKey("headers", $client_response);
        $this->assertEquals($response_headers, $client_response['headers'], "HTTP::post method did not return the expected headers");
        $this->assertArrayHasKey("body", $client_response);
        $this->assertEquals($response_body, $client_response['body'], "HTTP::post method did not return the expected body");

        // check the history
        foreach ($container as $history) {
            $this->assertEquals("POST", $history['request']->getMethod(), "HTTP::post method failed to use the GET request method");

            $this->assertEquals(["application/json"], $history['request']->getHeader("Accept"), "HTTP::post method failed to use the proper accept header");

            $this->assertEquals(["DN " . $public_key], $history['request']->getHeader("Authorization"), "HTTP::post method failed to use the proper authorization header");

            $this->assertEquals("https", $history['request']->getUri()->getScheme(), "HTTP::post method failed to use the proper scheme/protocol");

            $this->assertEquals("api.dealnews.com", $history['request']->getUri()->getHost(), "HTTP::post method failed to use the proper host");

            $this->assertEquals("/features", $history['request']->getUri()->getPath(), "HTTP::post method failed to use the proper url path");
        }
    }


    /**
     * Tests that post form data provided to the post method gets turned into proper form data
     */
    public function testPostWithPostData () {
        $public_key = "foo";
        $secret_key = "";

        $response_headers = [
            'Content-Type' => ["application/json", "charset=utf-8"],
        ];

        $response_body = "{'foo': 'bar'}";

        $container = [];
        $responses = [
            new Response(200, $response_headers, $response_body),
        ];

        $handler = $this->getGuzzleHandler($container, $responses);

        $http = new HTTP($public_key, $secret_key, "https://api.dealnews.com", $this->mockAuth($public_key, $secret_key), $handler);
        $client_response = $http->post("/login", ['username' => "foo", 'password' => "bar"]);

        // check the history
        foreach ($container as $history) {
            $this->assertEquals(["application/x-www-form-urlencoded"], $history['request']->getHeader("Content-Type"), "HTTP::post method failed to use the proper accept header");

            $this->assertEquals("username=foo&password=bar", $history['request']->getBody()->getContents(), "HTTP::post method failed to properly populate the form body");
        }
    }


    /**
     * Tests that the response format can be overridden on a per-request basis
     */
    public function testPostOverrideFormat () {
        $public_key = "foo";
        $secret_key = "";

        $response_headers = [
            'Content-Type' => ["application/json", "charset=utf-8"],
        ];

        $response_body = "{'foo': 'bar'}";

        $container = [];
        $responses = [
            new Response(200, $response_headers, $response_body),
        ];

        $handler = $this->getGuzzleHandler($container, $responses);

        $http = new HTTP($public_key, $secret_key, "https://api.dealnews.com", $this->mockAuth($public_key, $secret_key), $handler);
        $client_response = $http->post("/features", [], "xml");

        // check the history
        foreach ($container as $history) {
            $this->assertEquals(["text/xml,application/xml"], $history['request']->getHeader("Accept"), "HTTP::post method failed to use the proper accept header");
        }
    }


    protected function mockAuth ($public, $secret="") {
        $mock = $this->getMockBuilder('\DealNews\API\Client\Auth')->setConstructorArgs([$public, $secret])->getMock();
        if (!empty($secret)) {
            $mock->method("getAuth")->willReturn("DN " . $public . ":" . $secret);
        } else {
            $mock->method("getAuth")->willReturn("DN " . $public);
        }

        return $mock;
    }


    /**
     * Creates a guzzle mock and adds history middleware so you can see
     * what the dealnews api client attempted to send to the server
     *
     * @param   array   $container      A container for history to be recorded by the history middleware
     * @param   array   $responses      An array of GuzzleHttp\Psr7\Response objects
     *
     * @return HandlerStack
     */
    protected function getGuzzleHandler (&$container, $responses) {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler($responses);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        return $handler;
    }
}
