<?php

namespace App\Command;

class CreateBundleUtils
{
    const ROOT_DIR = '/lib/acme/foo-bundle/';
    const CONTROLLER_DIR = 'Controller/';
    const CONTROLLER_FILE = 'AcmeFooController.php';
    const DEPENDENCY_INJECTION_DIR = 'DependencyInjection/';
    const DEPENDENCY_INJECTION_FILE = 'AcmeFooExtension.php';
    const CONFIGURATION_FILE = 'Configuration.php';
    const ENTITY_DIR = 'Entity/';
    const RESOURCES_DIR = 'Resources/';
    const CONFIG_DIR = 'config/';
    const DOCTRINE_DIR = 'doctrine/';
    const ROUTING_DIR = 'routing/';
    const SERVICES_FILE = 'services.xml';
    const DOC_DIR = 'doc/';
    const INDEX_DOC_FILE = 'index.rst';
    const PUBLIC_DIR = 'public/';
    const JS_DIR = 'js/';
    const CSS_DIR = 'css/';
    const TRANSLATIONS_DIR = 'translations/';
    const MESSAGES_EN_FILE = 'messages.en.yml';
    const MESSAGES_ES_FILE = 'messages.es.yml';
    const VIEWS_DIR = 'views/';
    const SERVICE_DIR = 'Service/';
    const TEST_DIR = 'Test/';

    public static function getRootDir()
    {
        return self::ROOT_DIR;
    }

    public static function getControllerDir()
    {
        return self::getRootDir() . self::CONTROLLER_DIR;
    }

    public static function getControllerPath()
    {
        return self::getControllerDir() . self::CONTROLLER_FILE;
    }

    public static function getDependencyInjectionDir()
    {
        return self::getRootDir() . self::DEPENDENCY_INJECTION_DIR;
    }

    public static function getDependencyInjectionPath()
    {
        return self::getDependencyInjectionDir() . self::DEPENDENCY_INJECTION_FILE;
    }

    public static function getConfigurationPath()
    {
        return self::getDependencyInjectionDir() . self::CONFIGURATION_FILE;
    }

    public static function getEntityDir()
    {
        return self::getRootDir() . self::ENTITY_DIR;
    }

    public static function getResourcesDir()
    {
        return self::getRootDir() . self::RESOURCES_DIR;
    }

    public static function getConfigDir()
    {
        return self::getResourcesDir() . self::CONFIG_DIR;
    }

    public static function getDoctrineDir()
    {
        return self::getConfigDir() . self::DOCTRINE_DIR;
    }

    public static function getRoutingDir()
    {
        return self::getConfigDir() . self::ROUTING_DIR;
    }

    public static function getServicesPath()
    {
        return self::getConfigDir() . self::SERVICES_FILE;
    }

    public static function getDocDir()
    {
        return self::getResourcesDir() . self::DOC_DIR;
    }

    public static function getIndexDocPath()
    {
        return self::getDocDir() . self::INDEX_DOC_FILE;
    }

    public static function getPublicDir()
    {
        return self::getResourcesDir() . self::PUBLIC_DIR;
    }

    public static function getCssDir()
    {
        return self::getPublicDir() . self::CSS_DIR;
    }

    public static function getJsDir()
    {
        return self::getPublicDir() . self::JS_DIR;
    }

    public static function getTranslationsDir()
    {
        return self::getResourcesDir() . self::TRANSLATIONS_DIR;
    }

    public static function getMessagesEnPath()
    {
        return self::getTranslationsDir() . self::MESSAGES_EN_FILE;
    }

    public static function getMessagesEsPath()
    {
        return self::getTranslationsDir() . self::MESSAGES_ES_FILE;
    }

    public static function getViewsDir()
    {
        return self::getResourcesDir() . self::VIEWS_DIR;
    }

    public static function getServiceDir()
    {
        return self::getRootDir() . self::SERVICE_DIR;
    }

    public static function getTestDir()
    {
        return self::getRootDir() . self::TEST_DIR;
    }
}