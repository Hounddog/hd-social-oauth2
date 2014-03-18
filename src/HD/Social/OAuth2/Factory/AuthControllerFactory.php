<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Martin Shwalbe (http://hounddog.github.com)
 */

namespace HD\Social\OAuth2\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use HD\Social\OAuth2\Controller\AuthController;
use ZF\OAuth2\Controller\Exception;

use OAuth2\Server as OAuth2Server;
use HD\Social\OAuth2\GrantType\SocialCredentials;

use Hybrid_Auth;
use Zend\Mvc\Router\Http\TreeRouteStack;

class AuthControllerFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface                          $controllers
     * @return AuthController
     * @throws \ZF\OAuth2\Controller\Exception\RuntimeException
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator()->get('ServiceManager');
        $config   = $services->get('Configuration');

        if (!isset($config['zf-oauth2']['storage']) || empty($config['zf-oauth2']['storage'])) {
            throw new Exception\RuntimeException(
                'The storage configuration [\'zf-oauth2\'][\'storage\'] for OAuth2 is missing'
            );
        }

        $storage = $services->get($config['zf-oauth2']['storage']);

        $enforceState  = isset($config['zf-oauth2']['enforce_state'])  ? $config['zf-oauth2']['enforce_state']  : true;
        $allowImplicit = isset($config['zf-oauth2']['allow_implicit']) ? $config['zf-oauth2']['allow_implicit'] : false;

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $server = new OAuth2Server($storage, ['enforce_state' => $enforceState, 'allow_implicit' => $allowImplicit]);

        // Add the "Social Credentials" grant type (custo grant type)
        $server->addGrantType(new SocialCredentials($storage));

        $hybridAuthConfig = $config['social-oauth2'];
        $hybridAuthConfig['base_url'] = $this->getBaseUrl($services);

        $hybridauth = new Hybrid_Auth($hybridAuthConfig);

        return new AuthController($server, $hybridauth);
    }

    public function getBaseUrl(ServiceLocatorInterface $services)
    {
        $router = $services->get('Router');
        if (!$router instanceof TreeRouteStack) {
            throw new ServiceNotCreatedException(
                'TreeRouteStack is required to create a fully qualified base url for HybridAuth'
            );
        }

        $request = $services->get('Request');
        if (!$router->getRequestUri() && method_exists($request, 'getUri')) {
            $router->setRequestUri($request->getUri());
        }
        if (!$router->getBaseUrl() && method_exists($request, 'getBaseUrl')) {
            $router->setBaseUrl($request->getBaseUrl());
        }

        return $router->assemble(
            [],
            [
                'name' => 'oauth/social/hauth',
                'force_canonical' => true,
            ]
        );
    }
}
