<?php

namespace App\Command;

class CreateBundleUtils
{
    const ROOT_DIR = '/lib/acme/foo-bundle/';
    const MAIN_FILE = 'AcmeFooBundle.php';
    const CONTROLLER_DIR = 'Controller/';
    const CONTROLLER_FILE = 'AcmeFooController.php';
    const DEPENDENCY_INJECTION_DIR = 'DependencyInjection/';
    const EXTENSION_FILE = 'AcmeFooExtension.php';
    const CONFIGURATION_FILE = 'Configuration.php';
    const DTO_DIR = 'DTO/';
    const CAR_DTO_FILE = 'CarDTO.php';
    const CAR_DTO__BUILDER_FILE = 'CarDTOBuilder.php';
    const ENTITY_DIR = 'Entity/';
    const ENTITY_REPOSITORY_FILE = 'CarRepository.php';
    const ENTITY_FILE = 'Car.php';
    const RESOURCES_DIR = 'Resources/';
    const CONFIG_DIR = 'config/';
    const DOCTRINE_DIR = 'doctrine/';
    const ENTITY_ORM_FILE = 'Car.orm.xml';
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
    const WIDGET_FILE = 'acme_foo_widget.html.twig';
    const SERVICE_DIR = 'Service/';
    const SERVICE_FILE = 'Service.php';
    const TESTS_DIR = 'Tests/';
    const MOCKS_DIR = 'Mock/';
    const USER_MOCK_FILE = 'UserMock.php';
    const SERVICE_TEST_FILE = 'ServiceTest.php';
    const BOOTSTRAP_FILE = 'bootstrap.php';
    const COMPOSER_FILE = 'composer.json';
    const BUNDLES_DIR = 'config/';
    const BUNDLES_FILE = 'bundles.php';
    const PACKAGE_DIR = 'config/packages/';
    const PACKAGE_FILE = 'acme_foo.yaml';
    const README_FILE = 'README.md';
    const LICENSE_FILE = 'LICENSE';
    const GIT_IGNORE_FILE = '.gitignore';
    const TRAVIS_FILE = '.travis';
    const PHP_UNIT_FILE = 'phpunit.xml.dist';

    public static function getRootDir($projectDir)
    {
        return $projectDir . self::ROOT_DIR;
    }

    public static function getMainFilePath($projectDir)
    {
        return self::getRootDir($projectDir) . self::MAIN_FILE;
    }

    public static function getControllerDir($projectDir)
    {
        return self::getRootDir($projectDir) . self::CONTROLLER_DIR;
    }

    public static function getControllerPath($projectDir)
    {
        return self::getControllerDir($projectDir) . self::CONTROLLER_FILE;
    }

    public static function getDependencyInjectionDir($projectDir)
    {
        return self::getRootDir($projectDir) . self::DEPENDENCY_INJECTION_DIR;
    }

    public static function getExtensionPath($projectDir)
    {
        return self::getDependencyInjectionDir($projectDir) . self::EXTENSION_FILE;
    }

    public static function getConfigurationPath($projectDir)
    {
        return self::getDependencyInjectionDir($projectDir) . self::CONFIGURATION_FILE;
    }

    public static function getDTODir($projectDir)
    {
        return self::getRootDir($projectDir) . self::DTO_DIR;
    }

    public static function getCarDTOPath($projectDir)
    {
        return self::getDTODir($projectDir) . self::CAR_DTO_FILE;
    }

    public static function getCarDTOBuilderPath($projectDir)
    {
        return self::getDTODir($projectDir) . self::CAR_DTO__BUILDER_FILE;
    }

    public static function getEntityDir($projectDir)
    {
        return self::getRootDir($projectDir) . self::ENTITY_DIR;
    }

    public static function getEntityPath($projectDir)
    {
        return self::getEntityDir($projectDir) . self::ENTITY_FILE;
    }

    public static function getEntityRepositoryPath($projectDir)
    {
        return self::getEntityDir($projectDir) . self::ENTITY_REPOSITORY_FILE;
    }

    public static function getResourcesDir($projectDir)
    {
        return self::getRootDir($projectDir) . self::RESOURCES_DIR;
    }

    public static function getConfigDir($projectDir)
    {
        return self::getResourcesDir($projectDir) . self::CONFIG_DIR;
    }

    public static function getDoctrineDir($projectDir)
    {
        return self::getConfigDir($projectDir) . self::DOCTRINE_DIR;
    }

    public static function getEntityOrmPath($projectDir)
    {
        return self::getDoctrineDir($projectDir) . self::ENTITY_ORM_FILE;
    }

    public static function getRoutingDir($projectDir)
    {
        return self::getConfigDir($projectDir) . self::ROUTING_DIR;
    }

    public static function getServicesPath($projectDir)
    {
        return self::getConfigDir($projectDir) . self::SERVICES_FILE;
    }

    public static function getDocDir($projectDir)
    {
        return self::getResourcesDir($projectDir) . self::DOC_DIR;
    }

    public static function getIndexDocPath($projectDir)
    {
        return self::getDocDir($projectDir) . self::INDEX_DOC_FILE;
    }

    public static function getPublicDir($projectDir)
    {
        return self::getResourcesDir($projectDir) . self::PUBLIC_DIR;
    }

    public static function getCssDir($projectDir)
    {
        return self::getPublicDir($projectDir) . self::CSS_DIR;
    }

    public static function getJsDir($projectDir)
    {
        return self::getPublicDir($projectDir) . self::JS_DIR;
    }

    public static function getTranslationsDir($projectDir)
    {
        return self::getResourcesDir($projectDir) . self::TRANSLATIONS_DIR;
    }

    public static function getMessagesEnPath($projectDir)
    {
        return self::getTranslationsDir($projectDir) . self::MESSAGES_EN_FILE;
    }

    public static function getMessagesEsPath($projectDir)
    {
        return self::getTranslationsDir($projectDir) . self::MESSAGES_ES_FILE;
    }

    public static function getViewsDir($projectDir)
    {
        return self::getResourcesDir($projectDir) . self::VIEWS_DIR;
    }

    public static function getWidgetPath($projectDir)
    {
        return self::getViewsDir($projectDir) . self::WIDGET_FILE;
    }

    public static function getServiceDir($projectDir)
    {
        return self::getRootDir($projectDir) . self::SERVICE_DIR;
    }

    public static function getServicePath($projectDir)
    {
        return self::getServiceDir($projectDir) . self::SERVICE_FILE;
    }

    public static function getTestDir($projectDir)
    {
        return self::getRootDir($projectDir) . self::TESTS_DIR;
    }

    public static function getMockDir($projectDir)
    {
        return self::getTestDir($projectDir) . self::MOCKS_DIR;
    }

    public static function getUserMockPath($projectDir)
    {
        return self::getMockDir($projectDir) . self::USER_MOCK_FILE;
    }

    public static function getServiceTestPath($projectDir)
    {
        return self::getTestDir($projectDir) . self::SERVICE_TEST_FILE;
    }

    public static function getBootstrapPath($projectDir)
    {
        return self::getTestDir($projectDir) . self::BOOTSTRAP_FILE;
    }

    public static function getComposerPath($projectDir)
    {
        return self::getRootDir($projectDir) . self::COMPOSER_FILE;
    }

    public static function getReadmePath($projectDir)
    {
        return self::getRootDir($projectDir) . self::README_FILE;
    }

    public static function getLicensePath($projectDir)
    {
        return self::getRootDir($projectDir) . self::LICENSE_FILE;
    }

    public static function getGitIgnorePath($projectDir)
    {
        return self::getRootDir($projectDir) . self::GIT_IGNORE_FILE;
    }

    public static function getTravisPath($projectDir)
    {
        return self::getRootDir($projectDir) . self::TRAVIS_FILE;
    }

    public static function getPhpUnitPath($projectDir)
    {
        return self::getRootDir($projectDir) . self::PHP_UNIT_FILE;
    }

    public static  function getComposerMainFile($projectDir)
    {
        return $projectDir . '/' .  self::COMPOSER_FILE;
    }

    public static  function getBundlesMainFile($projectDir)
    {
        return $projectDir . '/' .  self::BUNDLES_DIR . self::BUNDLES_FILE;
    }

    public static  function getPackageMainFile($projectDir)
    {
        return $projectDir . '/' .  self::PACKAGE_DIR . self::PACKAGE_FILE;
    }
}