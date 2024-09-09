<?php

namespace Aropixel\PageBundle\DependencyInjection;

use Aropixel\PageBundle\Entity\PageInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class AropixelPageExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerParameters($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }


    private function registerParameters(ContainerBuilder $container, array $config)
    {
        $container->setParameter('aropixel_page.entities', $config['entities']);
        $container->setParameter('aropixel_page.entities.page', $config['entities'][PageInterface::class]);
        $container->setParameter('aropixel_page.forms', $config['forms']);
        $container->setParameter('aropixel_page.form.default', $config['forms']['default']);
        $container->setParameter('aropixel_page.form.default_translatable', $config['forms']['default_translatable']);
    }


    public function prepend(ContainerBuilder $container)
    {
        // get all bundles
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['DoctrineBundle'])) {
            $config = array_merge(...$container->getExtensionConfig('doctrine'));

            // do not register mappings if dbal not configured.
            if (!empty($config['dbal']) && !empty($config['orm'])) {
                $container->prependExtensionConfig('doctrine', array(
                    'orm' => array(
                        'mappings' => array(
                            'AropixelPageBundle' => array(
                                'is_bundle' => true,
                                'type' => 'xml',
                            ),
                        ),
                    ),
                ));
            }
        }

//        $configs = $container->getExtensionConfig($this->getAlias());
//        $config = $this->processConfiguration(new Configuration(), $configs);

    }
}
