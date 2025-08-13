<?php

namespace Tests\Unit;

use App\Models\Passport\Client;
use Tests\TestCase;

class PassportClientTest extends TestCase
{
    public function test_get_redirect_attribute_with_string_value()
    {
        $client = new Client;

        // Test with JSON string
        $jsonString = json_encode(['http://localhost:3000/callback', 'http://localhost:3001/callback']);
        $result     = $client->getRedirectAttribute($jsonString);

        $this->assertEquals(['http://localhost:3000/callback', 'http://localhost:3001/callback'], $result);

        // Test with single string
        $singleString = 'http://localhost:3000/callback';
        $result       = $client->getRedirectAttribute($singleString);

        $this->assertEquals(['http://localhost:3000/callback'], $result);

        // Test with invalid JSON string
        $invalidJson = 'invalid-json';
        $result      = $client->getRedirectAttribute($invalidJson);

        $this->assertEquals(['invalid-json'], $result);
    }

    public function test_get_redirect_attribute_with_array_value()
    {
        $client = new Client;

        $arrayValue = ['http://localhost:3000/callback', 'http://localhost:3001/callback'];
        $result     = $client->getRedirectAttribute($arrayValue);

        $this->assertEquals($arrayValue, $result);
    }

    public function test_get_redirect_attribute_with_null_value()
    {
        $client = new Client;

        $result = $client->getRedirectAttribute(null);

        $this->assertEquals([], $result);
    }

    public function test_set_redirect_attribute_with_array()
    {
        $client = new Client;

        $redirects = ['http://localhost:3000/callback', 'http://localhost:3001/callback'];
        $client->setRedirectAttribute($redirects);

        $this->assertEquals(json_encode($redirects), $client->getAttributes()['redirect']);
    }

    public function test_set_redirect_attribute_with_string()
    {
        $client = new Client;

        $redirect = 'http://localhost:3000/callback';
        $client->setRedirectAttribute($redirect);

        $this->assertEquals(json_encode([$redirect]), $client->getAttributes()['redirect']);
    }

    public function test_get_grant_types_attribute_with_string_value()
    {
        $client = new Client;

        // Test with JSON string
        $jsonString = json_encode(['authorization_code', 'refresh_token']);
        $result     = $client->getGrantTypesAttribute($jsonString);

        $this->assertEquals(['authorization_code', 'refresh_token'], $result);

        // Test with single string
        $singleString = 'authorization_code';
        $result       = $client->getGrantTypesAttribute($singleString);

        $this->assertEquals(['authorization_code'], $result);

        // Test with invalid JSON string
        $invalidJson = 'invalid-json';
        $result      = $client->getGrantTypesAttribute($invalidJson);

        $this->assertEquals(['invalid-json'], $result);
    }

    public function test_get_grant_types_attribute_with_array_value()
    {
        $client = new Client;

        $arrayValue = ['authorization_code', 'refresh_token'];
        $result     = $client->getGrantTypesAttribute($arrayValue);

        $this->assertEquals($arrayValue, $result);
    }

    public function test_get_grant_types_attribute_with_null_value()
    {
        $client = new Client;

        $result = $client->getGrantTypesAttribute(null);

        $this->assertEquals([], $result);
    }

    public function test_set_grant_types_attribute_with_array()
    {
        $client = new Client;

        $grantTypes = ['authorization_code', 'refresh_token'];
        $client->setGrantTypesAttribute($grantTypes);

        $this->assertEquals(json_encode($grantTypes), $client->getAttributes()['grant_types']);
    }

    public function test_set_grant_types_attribute_with_string()
    {
        $client = new Client;

        $grantType = 'authorization_code';
        $client->setGrantTypesAttribute($grantType);

        $this->assertEquals(json_encode([$grantType]), $client->getAttributes()['grant_types']);
    }

    public function test_casts_are_properly_defined()
    {
        $client = new Client;

        $expectedCasts = [
            'redirect_uris' => 'array',
            'grant_types'   => 'array',
            'revoked'       => 'boolean',
        ];

        $this->assertEquals($expectedCasts, $client->getCasts());
    }
}
