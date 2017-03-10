<?php
/**
 * directly copied from klein test suite
 */ 

namespace cbenco\Tests\APITests\Mocks;

use Klein\Request;
/**
 * MockRequestFactory
 *
 * Allow for the simple creation of mock requests
 * (great for testing... ;))
 */
class MockRequestFactory
{
    /**
     * Create a new mock request
     *
     * @param string $uri
     * @param string $req_method
     * @param array $parameters
     * @param array $cookies
     * @param array $server
     * @param array $files
     * @param string $body
     * @return void
     */
    public static function create(
        $uri = '/',
        $req_method = 'GET',
        $parameters = array(),
        $cookies = array(),
        $server = array(),
        $files = array(),
        $body = null
    ) {
        // Create a new Request object
        $request = new Request(
            array(),
            array(),
            $cookies,
            $server,
            $files,
            $body
        );
        // Reformat
        $req_method = strtoupper(trim($req_method));
        // Set its URI and Method
        $request->server()->set('REQUEST_URI', $uri);
        $request->server()->set('REQUEST_METHOD', $req_method);
        // Set our parameters
        switch ($req_method) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $request->paramsPost()->replace($parameters);
                break;
            default:
                $request->paramsGet()->replace($parameters);
                break;
        }
        return $request;
    }
}