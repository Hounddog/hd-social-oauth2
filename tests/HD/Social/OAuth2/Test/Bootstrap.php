<?php

namespace HD\Social\OAuth2\Test;

use Composer\Autoload\ClassLoader;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

class Bootstrap
{
    /**
     * @var array
     */
    public static $config;

    public static function init()
    {
        $vendorPath = static::findParentPath('vendor');

        $autoloaderPath = $vendorPath . '/autoload.php';

        if (! is_readable($autoloaderPath)) {
            throw new \RuntimeException("Autoloader could not be found. Did you run 'composer install --dev'?");
        }

        $loader = require $autoloaderPath;

        if (! $loader instanceof ClassLoader) {
            throw new \RuntimeException("Autoloader could not be found. Did you run 'composer install --dev'?");
        }

        $loader->add('ZendTest', $vendorPath . '/zendframework/zendframework/tests');

        static::$config = file_exists('./tests/config/test.application.config.php') ?
            require './tests/config/test.application.config.php'
            : require './tests/config/test.application.config.php.dist';
    }

    /**
     * Builds a new service manager
     *
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(
                isset(static::$config['service_manager']) ? static::$config['service_manager'] : []
            )
        );
        $serviceManager->setService('ApplicationConfig', static::$config);
        $serviceManager->setFactory('ServiceListener', 'Zend\Mvc\Service\ServiceListenerFactory');

        /** @var $moduleManager ModuleManager */
        $moduleManager = $serviceManager->get('ModuleManager');
        $moduleManager->loadModules();

        return $serviceManager;
    }

    /**
     * @param $path
     * @return bool|string
     */
    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }

        return $dir . '/' . $path;
    }
}
