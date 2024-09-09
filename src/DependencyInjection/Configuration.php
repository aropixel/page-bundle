<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Aropixel\PageBundle\Entity\Field;
use Aropixel\PageBundle\Entity\FieldInterface;
use Aropixel\PageBundle\Entity\FieldTranslation;
use Aropixel\PageBundle\Entity\FieldTranslationInterface;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Entity\PageTranslation;
use Aropixel\PageBundle\Entity\PageTranslationInterface;
use Aropixel\PageBundle\Form\Type\DefaultPageType;
use Aropixel\PageBundle\Form\Type\DefaultTranslatablePageType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder('aropixel_page');

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('entities')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode(PageInterface::class)->defaultValue(Page::class)->end()
            ->scalarNode(FieldInterface::class)->defaultValue(Field::class)->end()
            ->scalarNode(PageTranslationInterface::class)->defaultValue(PageTranslation::class)->end()
            ->scalarNode(FieldTranslationInterface::class)->defaultValue(FieldTranslation::class)->end()
            ->end()
            ->end()
            ->arrayNode('forms')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('default')->defaultValue(DefaultPageType::class)->end()
            ->scalarNode('default_translatable')->defaultValue(DefaultTranslatablePageType::class)->end()
            ->scalarNode('template_path')->defaultValue('@AropixelPage')->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
