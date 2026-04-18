<?php

namespace Aropixel\PageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Automatically registers page form types that extend AbstractJsonPageType.
 *
 * Any class tagged 'aropixel.page.form_type' (applied automatically via
 * #[AutoconfigureTag] on AbstractJsonPageType) is discovered here.
 * Its getType() return value is used as the type identifier and added
 * to the aropixel_page.forms parameter alongside the built-in types.
 */
class RegisterPageFormTypesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('aropixel.page.form_type');

        if (empty($taggedServices)) {
            return;
        }

        /** @var array<string, string> $forms */
        $forms = $container->getParameter('aropixel_page.forms');

        foreach ($taggedServices as $serviceId => $_tags) {
            $class = $container->getDefinition($serviceId)->getClass() ?? $serviceId;

            // Call getType() without constructor — safe because the method only returns a string constant.
            $type = (new \ReflectionClass($class))->newInstanceWithoutConstructor()->getType();

            $forms[$type] = $class;
        }

        $container->setParameter('aropixel_page.forms', $forms);
    }
}
