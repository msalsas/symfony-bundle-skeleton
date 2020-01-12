<?php

namespace Acme\FooBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class AcmeFooExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $config = $this->processConfiguration(new Configuration(), $configs);
        // TODO: Set custom parameters
         $container->setParameter('acme_foo.bar', $config['bar']);
         $container->setParameter('acme_foo.integer_foo', $config['integer_foo']);
         $container->setParameter('acme_foo.integer_bar', $config['integer_bar']);
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);
        // TODO: Set custom doctrine config
        $doctrineConfig = [];
        $doctrineConfig['orm']['resolve_target_entities']['Acme\FooBundle\Entity\UserInterface'] = $config['user_provider'];
        $doctrineConfig['orm']['mappings'][] = array(
            'name' => 'AcmeFooBundle',
            'is_bundle' => true,
            'type' => 'xml',
            'prefix' => 'Acme\FooBundle\Entity'
        );
        $container->prependExtensionConfig('doctrine', $doctrineConfig);
        // TODO: Set custom twig config
        $twigConfig = [];
        $twigConfig['globals']['acme_foo_bar_service'] = "@acme_foo.bar_service";
        $twigConfig['paths'][__DIR__.'/../Resources/views'] = "acme_foo";
        $twigConfig['paths'][__DIR__.'/../Resources/public'] = "acme_foo.public";
        $container->prependExtensionConfig('twig', $twigConfig);
    }

    public function getAlias()
    {
        return 'acme_foo';
    }
}