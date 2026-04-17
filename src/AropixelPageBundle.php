<?php

namespace Aropixel\PageBundle;

use Aropixel\PageBundle\DependencyInjection\Compiler\DoctrineTargetEntitiesResolverPass;
use Aropixel\PageBundle\DependencyInjection\Compiler\RegisterPageFormTypesPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AropixelPageBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrineTargetEntitiesResolverPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
        $container->addCompilerPass(new RegisterPageFormTypesPass());
    }
}
