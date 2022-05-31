<?php

namespace Worksection\OAuth2\Client\Test\Provider;

use Worksection\OAuth2\Client\Provider\Wrike;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class WrikeTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new Wrike([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }
    
    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        
        /* Required for Wrike API. Optional for Wrike API are: redirect_uri, state, scope */
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('response_type', $query);
    }
    
    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        
        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);
        
        $this->assertEquals('/oauth2/token', $uri['path']);
    }
    
    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        
        $this->assertEquals('/oauth2/authorize/v4', $uri['path']);
    }
    
    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')
            ->times(1)
            ->andReturn('application/json');
        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn('{"access_token":"mock_access_token","refresh_token":"mock_refresh_token","token_type":"bearer","expires_in":3600,"host":"mock_host"}');
            
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);
        
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getResourceOwnerId(), 'Wrike does not return user ID with access token. Expected null.');
    }
}
