<?php

namespace Aropixel\PageBundle\Form\Type;

use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Entity\PageTranslation;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base form type for all page types.
 *
 * It handles common fields like title, slug, and SEO metadata.
 */
abstract class AbstractPageType extends AbstractType implements PageFormTypeInterface
{
    /** @var string The class name of the Page entity */
    protected string $pageClass;

    /**
     * @return string The type identifier (e.g., 'default').
     */
    abstract public function getType(): string;

    public function __construct(
        private readonly ParameterBagInterface $parameterBag
    ) {
        $entities = $this->parameterBag->get('aropixel_page.entities');
        $this->pageClass = $entities[PageInterface::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $page = $builder->getData();
        $type = $page?->getType();

        // Common fields for all pages
        $builder
            ->add('title', TextType::class, ['label' => 'page.form.title'])
            ->add('slug', TextType::class, ['label' => 'page.form.slug', 'required' => false])
        ;

        if ($page && $page->getStaticCode()) {
            $builder->get('slug')->setDisabled(true);
        }

        // Translation logic for SEO fields
        if ('default_translatable' == $type) {
            $builder
                ->add('title', TranslatableType::class, [
                    'label' => 'page.form.title',
                    'personal_translation' => PageTranslation::class,
                    'property_path' => 'translations',
                ])
                ->add('slug', TranslatableType::class, [
                    'label' => 'page.form.slug',
                    'personal_translation' => PageTranslation::class,
                    'property_path' => 'translations',
                    'required' => false,
                ])
                ->add('metaTitle', TranslatableType::class, [
                    'label' => 'page.form.meta_title',
                    'personal_translation' => PageTranslation::class,
                    'property_path' => 'translations',
                    'required' => false,
                ])
                ->add('metaDescription', TranslatableType::class, [
                    'label' => 'page.form.meta_description',
                    'personal_translation' => PageTranslation::class,
                    'property_path' => 'translations',
                    'required' => false,
                ])
                ->add('metaKeywords', TranslatableType::class, [
                    'label' => 'page.form.meta_keywords',
                    'personal_translation' => PageTranslation::class,
                    'property_path' => 'translations',
                    'required' => false,
                ])
            ;
        } else {
            $builder
                ->add('metaTitle', null, ['label' => 'page.form.meta_title'])
                ->add('metaDescription', null, ['label' => 'page.form.meta_description'])
                ->add('metaKeywords', null, ['label' => 'page.form.meta_keywords'])
            ;
        }

        // Automatically set the page type from the form type
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $page = $event->getData();
            if ($page instanceof PageInterface) {
                $page->setType($this->getType());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->pageClass,
            'multiple' => false,
        ]);
    }
}
