<?php

namespace HD\Social\OAuth2\GrantType;

use OAuth2\ClientAssertionType\ClientAssertionTypeInterface;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;

class SocialCredentials implements GrantTypeInterface, ClientAssertionTypeInterface
{
    private $userInfo;

    protected $storage;

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
        if (!$request->request("user_id") || !$request->request("provider") || !$request->request("provider_id") || !$request->request("provider_access_token")) {
            $response->setError(400, 'invalid_request', 'Missing parameters: "username" and "provider" and "provider_id" and "access_token" required');

            return null;
        }

        if (!$this->storage->getUserProviderAccessToken($request->request("provider_access_token"), $request->request("provider"), $request->request("provider_id"), $request->request("user_id"))) {
            return null;
        }

        $userInfo = $this->storage->getUserDetails($request->request("user_id"));

        if (empty($userInfo)) {
            $response->setError(400, 'invalid_grant', 'Unable to retrieve user information');

            return null;
        }

        if (!isset($userInfo['user_id'])) {
            throw new \LogicException("you must set the user_id on the array returned by getUserDetails");
        }

        $this->userInfo = $userInfo;

        return true;
    }

    public function getClientId()
    {
        return 'fake';
    }

    public function getUserId()
    {
        return $this->userInfo['user_id'];
    }

    public function getScope()
    {
        return isset($this->userInfo['scope']) ? $this->userInfo['scope'] : null;
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        $includeRefreshToken = false;

        return $accessToken->createAccessToken($client_id, $user_id, $scope, $includeRefreshToken);
    }
}
