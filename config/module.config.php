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
        'invokables' => array(
            'HD\Social\OAuth2\Controller\Auth' => 'HD\Social\OAuth2\Controller\AuthController',
        ),
    ),
);