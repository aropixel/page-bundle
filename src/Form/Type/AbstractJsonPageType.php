<?php

namespace Aropixel\PageBundle\Form\Type;

use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Aropixel\PageBundle\Entity\PageTranslation;
use Aropixel\PageBundle\Form\DataMapper\JsonPageDataMapper;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Base class for custom page forms that store data in jsonContent.
 *
 * Any concrete class extending this will be automatically discovered
 * and registered as a page form type — no YAML declaration needed.
 */
#[AutoconfigureTag('aropixel.page.form_type')]
abstract class AbstractJsonPageType extends AbstractPageType
{
    public function __construct(
        ParameterBagInterface $parameterBag,
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($parameterBag);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // First, add common fields (title, slug, SEO) from AbstractPageType
        parent::buildForm($builder, $options);

        // Then, add custom fields in the child class
        $this->buildCustomForm($builder, $options);

        // Make slug required for non-fixed pages (fixed pages have a staticCode and
        // their slug is managed by the system).
        $page = $builder->getData();
        if (null === $page?->getStaticCode()) {
            $builder->add('slug', TranslatableType::class, [
                'label'       => 'page.form.slug',
                'personal_translation' => PageTranslation::class,
                'property_path' => 'translations',
                'required'    => false,
            ]);
        }

        // Set the data mapper with the current locale so it can write translatable
        // fields (slug, metaTitle, jsonContent, …) directly to the PageTranslation
        // collection for the right locale.
        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';
        $builder->setDataMapper(new JsonPageDataMapper(
            $this->getSlugSource(),
            $locale,
            $this->getTranslationClass(),
        ));
    }

    /**
     * Override this method to add custom fields.
     */
    abstract protected function buildCustomForm(FormBuilderInterface $builder, array $options): void;

    /**
     * @return string The type identifier (e.g., 'contact').
     */
    abstract public function getType(): string;

    /**
     * Returns the form field name whose value drives Gedmo slug generation,
     * i.e. the field whose value is also written to $page->setTitle() so that
     * Gedmo Sluggable produces a valid entity-column slug.
     *
     * Default: 'title'. Override with e.g. 'name' if your form uses a
     * different field as the "name" of the page:
     *
     *   protected function getSlugSource(): ?string { return 'name'; }
     *
     * Return null to disable automatic title feeding entirely.
     */
    protected function getSlugSource(): ?string
    {
        return 'title';
    }

    /**
     * Returns the FQCN of the PersonalTranslation entity used to store
     * per-locale data. Override this if your application uses a custom
     * translation entity that extends PageTranslation.
     */
    protected function getTranslationClass(): string
    {
        return PageTranslation::class;
    }
}
