<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace HD\Social\OAuth2\Adapter;

use ZF\OAuth2\Adapter\PdoAdapter as ZFOAuthPdoAdapter;

/**
 * Extension of OAuth2\Storage\PDO that provides Bcrypt client_secret/password
 * encryption
 */
class PdoAdapter extends ZFOAuthPdoAdapter
{
    /**
     * @param string $connection
     * @param array  $config
     */
    public function __construct($connection, $config = [])
    {
        $config = [
            'user_provider_table' => 'oauth_user_provider',
            'user_provider_access_token_table' => 'oauth_user_provider_access_tokens'
        ];
        parent::__construct($connection, $config);
    }

    public function setUserProvider($provider, $provider_id, $username)
    {
        // if it exists, update it.
        if ($this->getUserProvider($username, $provider)) {
            $stmt = $this->db->prepare(sprintf(
                'UPDATE %s SET provider_id=:provider_id where user_id=:username and provider=:provider',
                $this->config['user_provider_table']
            ));
        } else {
            $stmt = $this->db->prepare(sprintf(
                'INSERT INTO %s (provider, provider_id, user_id) VALUES (:provider, :provider_id, :username)',
                $this->config['user_provider_table']
            ));
        }

        return $stmt->execute(compact('provider', 'provider_id', 'username'));
    }

    public function getUserProvider($username, $provider)
    {
        $stmt = $this->db->prepare(
            $sql = sprintf(
                'SELECT * from %s where user_id=:username and provider=:provider',
                $this->config['user_provider_table']
            )
        );
        $stmt->execute([
            'username' => $username,
            'provider' => $provider
        ]);

        if (!$providerInfo = $stmt->fetch()) {
            return false;
        }

        // the default behavior is to use "username" as the user_id
        return array_merge([
            'user_id' => $username
        ], $providerInfo);
    }

    public function setUserProviderAccessToken(
        $access_token,
        $provider,
        $provider_id,
        $user_id,
        $expires = null,
        $scope = null
    ) {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getUserProviderAccessToken($access_token, $provider, $provider_id, $user_id)) {
            $stmt = $this->db->prepare(sprintf(
                'UPDATE %s SET provider=:provider,'
                . 'provider_id=:provider_id, user_id=:user_id, expires=:expires,'
                . 'scope=:scope where access_token=:access_token',
                $this->config['user_provider_access_token_table']
            ));
        } else {
            $stmt = $this->db->prepare(sprintf(
                'INSERT INTO %s (access_token, provider, provider_id, user_id, expires, scope)'
                . ' VALUES (:access_token, :provider, :provider_id, :user_id, :expires, :scope)',
                $this->config['user_provider_access_token_table']
            ));
        }

        return $stmt->execute(compact('access_token', 'provider', 'provider_id', 'user_id', 'expires', 'scope'));
    }

    public function getUserProviderAccessToken($access_token, $provider, $provider_id, $user_id)
    {
        $stmt = $this->db->prepare(sprintf(
            'SELECT * from %s where access_token=:access_token '
            . 'and provider=:provider and provider_id=:provider_id and user_id=:user_id',
            $this->config['user_provider_access_token_table']
        ));

        $token = $stmt->execute(compact('access_token', 'provider', 'provider_id', 'user_id'));

        if ($token = $stmt->fetch()) {
            // convert date string back to timestamp
            $token['expires'] = strtotime($token['expires']);
        }

        return $token;
    }

    /**
     * Set the user
     *
     * @param  string $username
     * @param  string $password
     * @param  string $firstName
     * @param  string $lastName
     * @return bool
     */
    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        // do not store in plaintext, use bcrypt
        $this->createBcryptHash($password);

        // if it exists, update it.
        if ($this->getUser($username)) {
            $stmt = $this->db->prepare(sprintf(
                'UPDATE %s SET password=:password, first_name=:firstName, last_name=:lastName where username=:username',
                $this->config['user_table']
            ));
        } else {
            $stmt = $this->db->prepare(sprintf(
                'INSERT INTO %s (username, password, first_name, last_name) '
                . 'VALUES (:username, :password, :firstName, :lastName)',
                $this->config['user_table']
            ));
        }

        return $stmt->execute(compact('username', 'password', 'firstName', 'lastName'));
    }
}
