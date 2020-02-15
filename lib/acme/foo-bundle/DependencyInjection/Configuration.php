<?php

namespace Acme\FooBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder $builder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('acme_foo');

        $rootNode = $builder->getRootNode();
        $rootNode->children()
            ->scalarNode('user_provider')
                ->isRequired()
                ->defaultValue('\App\Entity\User')
            ->end()
            ->arrayNode('bar')
                ->isRequired()
                ->scalarPrototype()
                    ->defaultValue([
                        'acme_foo.ipsum',
                        'acme_foo.lorem',
                    ])
                ->end()
            ->end()
            ->integerNode('integer_foo')
                ->isRequired()
                ->defaultValue(2)
                ->min(1)
            ->end()
            ->integerNode('integer_bar')
                ->isRequired()
                ->defaultValue(50)
                ->min(1)
            ->end()
            ->end();

        return $builder;
    }
}
