<?php

namespace Aropixel\PageBundle\Form\Type;

use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Form\DataMapper\PageFieldDataMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


abstract class AbstractPageType extends AbstractType implements PageFormTypeInterface
{

    const ALL_RIGHTS_MODE = 'ROLE_SUPER_ADMIN';

    /** @var AuthorizationCheckerInterface */
    private $securityContext;

    /** @var PageFieldDataMapper  */
    private $pageFieldDataMapper;

    /** @var string */
    protected $pageClass;


    abstract public function getType() : string;


    /**
     * AbstractPageType constructor.
     * @param AuthorizationCheckerInterface $securityContext
     * @param PageFieldDataMapper $pageFieldDataMapper
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(AuthorizationCheckerInterface $securityContext, PageFieldDataMapper $pageFieldDataMapper, ParameterBagInterface $parameterBag)
    {
        //
        $this->securityContext = $securityContext;
        $this->pageFieldDataMapper = $pageFieldDataMapper;

        //
        $entities = $parameterBag->get('aropixel_page.entities');
        $this->pageClass = $entities[PageInterface::class];
    }




    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($builder->getData() && $builder->getData()->getType()=='default') {
            $builder
                ->add('title', TextType::class, array('label'  => 'Nom de la page'))
                ->add('slug', HiddenType::class)
            ;
        }


        // Si le titre n'est pas autorisé et qu'on est pas super admin,
        $builder
            ->add('metaTitle', null, array('label'  => 'Meta title'))
            ->add('metaDescription', null, array('label'  => 'Meta description'))
            ->add('metaKeywords', null, array('label'  => 'Meta keywords'))
        ;

        $builder
            ->setDataMapper($this->pageFieldDataMapper)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->pageClass,
            'multiple' => false,
        ]);
    }
}
