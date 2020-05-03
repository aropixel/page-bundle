<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('aropixel_page');

        $treeBuilder->getRootNode()
                    ->children()

                        ->arrayNode('blocks')
                            ->useAttributeAsKey('block_code')
                            ->arrayPrototype()
                            ->children()
                                ->scalarNode('page')->end()
                                ->scalarNode('name')->end()
                                ->arrayNode('inputs')
                                    ->useAttributeAsKey('input_code')
                                    ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('type')
                                            ->validate()
                                                ->ifNotInArray(['Text', 'Textarea', 'Tabs'])
                                                ->thenInvalid("Le type doit être 'Text', 'Textarea' ou 'Tabs'")
                                            ->end()
                                        ->end()
                                        ->scalarNode('label')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()


        ;


        // je veux pouvoir utiliser mes entités dans un array nommé "entities"
        // mais qui n'existe pas dans la config yaml

        //$crops['entities'];
        //$crops['types'];

        /*$treeBuilder->getRootNode()
            ->children()
                ->arrayNode('crops')
                    ->defaultValue(array())
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('entities')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('image')->end()
                    ->end()
                ->end()
            ->end()
        ;*/

        return $treeBuilder;
    }
}
