<?php

namespace Aropixel\PageBundle\Form;

use Aropixel\AdminBundle\Form\Type\Image\Gallery\GalleryType;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\PageBundle\Entity\PageGallery;
use Aropixel\PageBundle\Entity\PageGalleryCrop;
use Aropixel\PageBundle\Entity\PageImage;
use Aropixel\PageBundle\Entity\PageImageCrop;
use Aropixel\PageBundle\Entity\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class PageType extends AbstractType
{

    const ALL_RIGHTS_MODE = 'ROLE_HYPER_ADMIN';

    /**
     * @var AuthorizationCheckerInterface
     */
    private $securityContext;



    /**
     * PageType constructor.
     * @param AuthorizationCheckerInterface $securityContext
     */
    public function __construct(AuthorizationCheckerInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }



    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var \Aropixel\PageBundle\Entity\Page $page */
        $page = $builder->getData();


        // Si le titre n'est pas autorisé et qu'on est pas super admin,
        // on l'affiche mais on le passe en disabled
        $titleParams = array('label'  => 'Titre');
        if ($page->getId() && !$page->getIsPageTitleEnabled() && !$this->securityContext->isGranted('ROLE_HYPER_ADMIN')) {
            $titleParams['disabled'] = 'true';
        }


        $builder
            ->add('title', TextareaType::class, $titleParams)
            ->add('metaTitle', null, array('label'  => 'Meta title'))
            ->add('metaDescription', null, array('label'  => 'Meta description'))
            ->add('metaKeywords', null, array('label'  => 'Meta keywords'))
            ->add('createdAt', DateTimeType::class, array(
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
            ))
        ;

        // Si on est en création
        if (!$page) {

            $builder
                ->add('excerpt', null, array('label'  => 'Chapeau'))
                ->add('description', null, array('label'  => 'Description', 'attr' => array('class' => 'ckeditor')))
                ->add('image', ImageType::class, array(
                    'data_class' => PageImage::class,
                    'crop_class' => PageImageCrop::class,
                ))
                ->add('status', ChoiceType::class, array(
                    'choices'  => array(
                        'Oui' => 'online',
                        'Non' => 'offline',
                    ),
                    'empty_data' => 'Non',
                    'expanded' => true
                ))
            ;

        }
        // Si on est en modification
        else {


            // Affiche le champs RESUME, seulement si:
            // - on est super admin
            // - ou si le champs est autorisé
            if ($this->securityContext->isGranted(self::ALL_RIGHTS_MODE) || $page->getIsPageExcerptEnabled()) {
                $builder
                    ->add('excerpt', null, array('label'  => 'Chapeau'))
                ;
            }


            // Affiche le champs DESCRIPTION, seulement si:
            // - on est super admin
            // - ou si le champs est autorisé
            if ($this->securityContext->isGranted(self::ALL_RIGHTS_MODE) || $page->getIsPageDescriptionEnabled()) {
                $builder
                    ->add('description', null, array('label'  => 'Description', 'attr' => array('class' => 'ckeditor')))
                ;
            }


            // Affiche le champs IMAGE, seulement si:
            // - on est super admin
            // - ou si le champs est autorisé
            if ($this->securityContext->isGranted(self::ALL_RIGHTS_MODE) || $page->getIsPageImageEnabled()) {
                $builder
                    ->add('image', ImageType::class, array(
                        'data_class' => PageImage::class,
                        'crop_class' => PageImageCrop::class,
                    ))
                ;
            }

            // Affiche le champs GALLERY, seulement si:
            // - on est super admin
            // - ou si le champs est autorisé
            if ($this->securityContext->isGranted(self::ALL_RIGHTS_MODE) || $page->getIsPageGalleryEnabled()) {
                $builder
                    ->add('gallery', GalleryType::class, array(
                        'entry_options' => array(
                            'data_class' => PageGallery::class,
                            'crop_class' => PageGalleryCrop::class,
                        )
                    ))
                ;
            }


            // Affiche le slug et le status seulement si
            // ce n'est pas une page fixe (avec un code)
            if (!$page->getCode()) {

                $builder
                    ->add('slug', HiddenType::class)
                    ->add('status', ChoiceType::class, array(
                        'choices'  => array(
                            'Oui' => 'online',
                            'Non' => 'offline',
                        ),
                        'empty_data' => 'Non',
                        'expanded' => true
                    ))
                ;

            }


        }


        // Ce champs n'est accessible (en création ou modification)
        // seulement par le super administrateur
        if($this->securityContext->isGranted(self::ALL_RIGHTS_MODE))
        {
            $builder
                ->add('code')
                ->add('name', null, array('label'  => 'Nom de la page'))
                ->add('isPageTitleEnabled', null, array(
                    'label'  => 'Afficher le champs titre',
                    'attr' => array('class' => 'styled')
                ))
                ->add('isPageExcerptEnabled', null, array(
                    'label'  => 'Afficher le champs résumé',
                    'attr' => array('class' => 'styled')
                ))
                ->add('isPageDescriptionEnabled', null, array(
                    'label'  => 'Afficher le champs description',
                    'attr' => array('class' => 'styled')
                ))
                ->add('isPageImageEnabled', null, array(
                    'label'  => 'Afficher le bloc image à la une',
                    'attr' => array('class' => 'styled')
                ))
                ->add('isPageGalleryEnabled', null, array(
                    'label'  => 'Autoriser les galleries',
                    'attr' => array('class' => 'styled')
                ))

            ;
        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
