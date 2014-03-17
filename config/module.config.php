<?php

return array(
    'router' => array(
        'routes' => array(
            'oauth' => array(
                'child_routes' => array(
                    'social' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/social',
                            'defaults' => array(
                                'controller' => 'HD\Social\OAuth2\Controller\Auth',
                            ),
                        ),
                        'child_routes' => array(
                            'provider' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:provider',
                                    'constraints' => array(
                                        'provider' => '[a-zA-Z][a-zA-Z0-9_-]+',
                                    ),
                                    'defaults' => array(
                                        'action' => 'provider',
                                    ),
                                ),
                            ),
                            'hauth' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/hauth',
                                    'constraints' => array(
                                        'provider' => '[a-zA-Z][a-zA-Z0-9_-]+',
                                    ),
                                    'defaults' => array(
                                        'action' => 'hybrid',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'controllers' => array(
        'factories' => array(
            'HD\Social\OAuth2\Controller\Auth' => 'HD\Social\OAuth2\Factory\AuthControllerFactory',
        ),
    ),

    'service_manager' => array(
        'factories' => array(
            'ZF\OAuth2\Adapter\PdoAdapter' => 'HD\Social\OAuth2\Factory\PdoAdapterFactory',
        )
    ),

    'social-oauth2' => array(
        "providers" => array (
            // openid providers
            "Instagram" => array (
                'wrapper' => array(
                    'class' => 'Hybrid_Providers_Instagram',
                    'path' => realpath(__DIR__ . '/../src/HD/Social/OAuth2/HybridAuth/Provider/Instagram.php'),
                ),
            ),
        ),
    )
);
