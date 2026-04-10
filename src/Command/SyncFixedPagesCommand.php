<?php

namespace Aropixel\PageBundle\Command;

use Aropixel\PageBundle\Entity\PageInterface;
use Aropixel\PageBundle\Repository\PageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'aropixel:page:sync-fixed',
    description: 'Sync fixed pages from configuration to database',
)]
class SyncFixedPagesCommand extends Command
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly ParameterBagInterface $parameterBag
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fixedPages = $this->parameterBag->get('aropixel_page.fixed_pages');
        $entities = $this->parameterBag->get('aropixel_page.entities');
        $pageClass = $entities[PageInterface::class];

        foreach ($fixedPages as $code => $config) {
            $page = $this->pageRepository->findOneBy(['staticCode' => $code]);

            if (!$page) {
                /** @var PageInterface $page */
                $page = new $pageClass();
                $page->setStaticCode($code);
                $page->setTitle($config['title']);
                $page->setType($config['type']);
                $page->setIsDeletable($config['deletable']);
                
                $this->pageRepository->add($page, true);
                $io->success(sprintf('Page "%s" created with code "%s".', $config['title'], $code));
            } else {
                // Update existing fixed page if needed
                $page->setIsDeletable($config['deletable']);
                $page->setType($config['type']);
                $this->pageRepository->add($page, true);
                $io->info(sprintf('Page with code "%s" already exists, updated configuration.', $code));
            }
        }

        return Command::SUCCESS;
    }
}
