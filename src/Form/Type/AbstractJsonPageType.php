<?php

namespace Aropixel\PageBundle\Form\Type;

use Aropixel\PageBundle\Form\DataMapper\JsonPageDataMapper;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base class for custom page forms that store data in jsonContent.
 *
 * Any concrete class extending this will be automatically discovered
 * and registered as a page form type — no YAML declaration needed.
 */
#[AutoconfigureTag('aropixel.page.form_type')]
abstract class AbstractJsonPageType extends AbstractPageType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // First, add common fields (title, slug, SEO) from AbstractPageType
        parent::buildForm($builder, $options);

        // Then, add custom fields in the child class
        $this->buildCustomForm($builder, $options);

        // Finally, set the data mapper to handle jsonContent
        $builder->setDataMapper(new JsonPageDataMapper());
    }

    /**
     * Override this method to add custom fields.
     */
    abstract protected function buildCustomForm(FormBuilderInterface $builder, array $options): void;

    /**
     * @return string The type identifier (e.g., 'contact').
     */
    abstract public function getType(): string;
}
