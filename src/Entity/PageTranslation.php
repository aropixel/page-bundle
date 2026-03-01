<?php

namespace Aropixel\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;

/**
 * Entity for storing page translations (Gedmo Personal Log).
 */
#[ORM\MappedSuperclass]
#[ORM\Table(name: 'aropixel_page_translation')]
#[ORM\Index(name: 'page_translation_idx', columns: ['locale', 'object_id', 'field'])]
#[ORM\Entity(repositoryClass: TranslationRepository::class)]
class PageTranslation extends AbstractPersonalTranslation implements PageTranslationInterface
{
    /**
     * @param string      $locale Locale of the translation (e.g., 'en', 'fr').
     * @param string      $field  the property name being translated
     * @param string|null $value  the translated content
     */
    public function __construct(string $locale, string $field, ?string $value = null)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 20)]
    protected $locale;

    #[ORM\Column(type: 'string', length: 32)]
    protected $field;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $content;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;
}
