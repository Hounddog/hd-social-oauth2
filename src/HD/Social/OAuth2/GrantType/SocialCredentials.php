<?php 

namespace HD\Social\OAuth2\GrantType;

use OAuth2\ClientAssertionType\ClientAssertionTypeInterface;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;

class SocialCredentials implements GrantTypeInterface, ClientAssertionTypeInterface
{
    public function __construct($storage, array $config = array())
    {
        $this->storage = $storage;
        $this->config = array_merge(array(
            'allow_credentials_in_request_body' => true,
            'allow_public_clients' => true,
        ), $config);
    }

    public function getQuerystringIdentifier()
    {
        return 'social_login';
    }

	public function validateRequest(RequestInterface $request, ResponseInterface $response)
	{
        /*echo 'validateRequest';
        echo '<pre>';
        print_r($request);
        */
        return true;
	}

    public function getClientId() 
    {
        //echo 'getClientId';
        //exit;
        return 'test';
    }
    
    public function getUserId()
    {
        return 'martin';
    }

    public function getScope()
    {
        //echo 'getScope';
        //exit;
        return null;
    }
    
    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        $includeRefreshToken = false;
        
    	return $accessToken->createAccessToken($client_id, $user_id, $scope, $includeRefreshToken);
    }

    private function loadClientData()
    {
        echo 'loadClientData';
        exit;
        if (!$this->clientData) {
            $this->clientData = $this->storage->getClientDetails($this->getClientId());
        }
    }
}