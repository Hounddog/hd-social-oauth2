<?php

return [
    'router' => [
        'routes' => [
            'oauth' => [
                'child_routes' => [
                    'social' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/social',
                            'defaults' => [
                                'controller' => 'HD\Social\OAuth2\Controller\Auth',
                            ],
                        ],
                        'child_routes' => [
                            'provider' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/:provider',
                                    'constraints' => [
                                        'provider' => '[a-zA-Z][a-zA-Z0-9_-]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'provider',
                                    ],
                                ],
                            ],
                            'hauth' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/hauth',
                                    'constraints' => [
                                        'provider' => '[a-zA-Z][a-zA-Z0-9_-]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'hybrid',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            'HD\Social\OAuth2\Controller\Auth' => 'HD\Social\OAuth2\Factory\AuthControllerFactory',
        ],
    ],

    'service_manager' => [
        'factories' => [
            'ZF\OAuth2\Adapter\PdoAdapter' => 'HD\Social\OAuth2\Factory\PdoAdapterFactory',
        ]
    ],

    'social-oauth2' => [
        "providers" => [
            // openid providers
            "Instagram" => [
                'wrapper' => [
                    'class' => 'HD\Social\OAuth2\HybridAuth\Provider\Instagram',
                    'path' => realpath(__DIR__ . '/../src/HD/Social/OAuth2/HybridAuth/Provider/Instagram.php'),
                ],
            ],
        ],
    ]
];
