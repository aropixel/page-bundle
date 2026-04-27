<?php

namespace Aropixel\PageBundle\Tests\DataFixtures;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PageFixture extends Fixture
{
    private const BUILDER_JSON = '{"sections":[{"visibleDesktop":true,"visibleMobile":true,"layout":"container","rows":[{"columns":[{"width":{"xl":"1-1"},"blocks":[{"type":"title","value":"Our services","tag":"h2"},{"type":"text","value":"<p>Discover our services.</p>"}]}]}]}]}';

    private const CONTACT_JSON = '{"phone":"+33 1 23 45 67 89","address":"1 rue de la Paix, 75001 Paris"}';

    public function load(ObjectManager $manager): void
    {
        $pages = $this->buildPages();

        foreach ($pages as $page) {
            $manager->persist($page);
        }

        $manager->flush();
    }

    /**
     * @return array<Page>
     */
    private function buildPages(): array
    {
        $homepage = new Page();
        $homepage->setType(Page::TYPE_DEFAULT);
        $homepage->setStaticCode('homepage');
        $homepage->setIsDeletable(false);
        $homepage->setStatus(Publishable::STATUS_ONLINE);
        $homepage->setTitle('Home');
        $homepage->setHtmlContent('<p>Welcome to our website.</p>');
        $this->addPageTranslations($homepage, [
            'fr' => ['title' => 'Accueil', 'htmlContent' => '<p>Bienvenue sur notre site.</p>'],
            'en' => ['title' => 'Home', 'htmlContent' => '<p>Welcome to our website.</p>'],
            'de' => ['title' => 'Startseite', 'htmlContent' => '<p>Willkommen auf unserer Website.</p>'],
            'es' => ['title' => 'Inicio', 'htmlContent' => '<p>Bienvenido a nuestro sitio web.</p>'],
            'it' => ['title' => 'Home', 'htmlContent' => '<p>Benvenuto nel nostro sito web.</p>'],
            'cs' => ['title' => 'Domů', 'htmlContent' => '<p>Vítejte na našem webu.</p>'],
        ]);
        $this->addReference('page-accueil', $homepage);

        $about = new Page();
        $about->setType(Page::TYPE_DEFAULT);
        $about->setStatus(Publishable::STATUS_ONLINE);
        $about->setTitle('About us');
        $about->setHtmlContent('<p>Learn more about us.</p>');
        $this->addPageTranslations($about, [
            'fr' => ['title' => 'À propos', 'htmlContent' => '<p>Apprenez-en plus sur nous.</p>'],
            'en' => ['title' => 'About us', 'htmlContent' => '<p>Learn more about us.</p>'],
            'de' => ['title' => 'Über uns', 'htmlContent' => '<p>Erfahren Sie mehr über uns.</p>'],
            'es' => ['title' => 'Acerca de nosotros', 'htmlContent' => '<p>Conozca más sobre nosotros.</p>'],
            'it' => ['title' => 'Chi siamo', 'htmlContent' => '<p>Scopri di più su di noi.</p>'],
            'cs' => ['title' => 'O nás', 'htmlContent' => '<p>Zjistěte o nás více.</p>'],
        ]);
        $this->addReference('page-apropos', $about);

        $services = new Page();
        $services->setType(Page::TYPE_BUILDER);
        $services->setStatus(Publishable::STATUS_ONLINE);
        $services->setTitle('Services');
        $services->setJsonContent(self::BUILDER_JSON);
        $this->addPageTranslations($services, [
            'fr' => ['title' => 'Services'],
            'en' => ['title' => 'Services'],
            'de' => ['title' => 'Leistungen'],
            'es' => ['title' => 'Servicios'],
            'it' => ['title' => 'Servizi'],
            'cs' => ['title' => 'Služby'],
        ]);
        $this->addReference('page-services', $services);

        $contact = new Page();
        $contact->setType('contact');
        $contact->setStaticCode('contact');
        $contact->setIsDeletable(false);
        $contact->setStatus(Publishable::STATUS_ONLINE);
        $contact->setTitle('Contact');
        $contact->setJsonContent(self::CONTACT_JSON);
        $this->addPageTranslations($contact, [
            'fr' => ['title' => 'Contact'],
            'en' => ['title' => 'Contact'],
            'de' => ['title' => 'Kontakt'],
            'es' => ['title' => 'Contacto'],
            'it' => ['title' => 'Contatto'],
            'cs' => ['title' => 'Kontakt'],
        ]);
        $this->addReference('page-contact', $contact);

        $offers = new Page();
        $offers->setType(Page::TYPE_DEFAULT);
        $offers->setStatus(Publishable::STATUS_ONLINE);
        $offers->setTitle('Our offers');
        $offers->setHtmlContent('<p>Discover our offers.</p>');
        $offers->setParent($services);
        $this->addPageTranslations($offers, [
            'fr' => ['title' => 'Nos offres', 'htmlContent' => '<p>Découvrez nos offres.</p>'],
            'en' => ['title' => 'Our offers', 'htmlContent' => '<p>Discover our offers.</p>'],
            'de' => ['title' => 'Unsere Angebote', 'htmlContent' => '<p>Entdecken Sie unsere Angebote.</p>'],
            'es' => ['title' => 'Nuestras ofertas', 'htmlContent' => '<p>Descubra nuestras ofertas.</p>'],
            'it' => ['title' => 'Le nostre offerte', 'htmlContent' => '<p>Scopri le nostre offerte.</p>'],
            'cs' => ['title' => 'Naše nabídky', 'htmlContent' => '<p>Objevte naše nabídky.</p>'],
        ]);

        return [$homepage, $about, $services, $contact, $offers];
    }

    /**
     * @param array<string, array<string, string>> $translations
     */
    private function addPageTranslations(Page $page, array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            foreach ($fields as $field => $value) {
                $page->addTranslation(new PageTranslation($locale, $field, $value));
            }
        }
    }
}
