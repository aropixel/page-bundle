<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class AropixelPageExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerParameters($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('orm.yml');
        $loader->load('services.yml');
    }


    private function registerParameters(ContainerBuilder $container, array $config)
    {
        //
        $container->setParameter('aropixel_page.entity', $config['entity']);

        //
        $configuredBlocks = [];

        // put the key (the code of the block) as a value inside the block's array
        foreach ($config['blocks'] as $configuredBlockKey => $configuredBlock) {
            $configuredBlocks[$configuredBlockKey] = $configuredBlock;
            $configuredBlocks[$configuredBlockKey]['code'] = $configuredBlockKey;
        }

        $container->setParameter('aropixel_page.blocks', $configuredBlocks);
    }
}
