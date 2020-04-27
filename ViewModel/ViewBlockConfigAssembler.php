<?php


namespace Aropixel\PageBundle\ViewModel;

use Aropixel\PageBundle\Repository\BlockRepository;
use Aropixel\PageBundle\Repository\PageRepository;

class ViewBlockConfigAssembler implements ViewBlockConfigAssemblerInterface
{
    /**
     * @var BlockRepository
     */
    private $blockRepository;
    /**
     * @var PageRepository
     */
    private $pageRepository;


    public function __construct(
        BlockRepository $blockRepository,
        PageRepository $pageRepository
    )
    {
        $this->blockRepository = $blockRepository;
        $this->pageRepository = $pageRepository;
    }

    public function create($block): ViewBlock
    {
        $viewBlock = new ViewBlock();
        $viewBlock->setName( $block['name'] );
        $viewBlock->setCode($block['code']);

        $page = $this->pageRepository->findOneBy( [ 'code' => $block['page'] ] );

        if (!is_null( $page ) ) {
            $viewBlock->setPageName( $page->getTitle() );
        }

        $viewBlock->setIsPersisted(false);

        $idBlockDb = $this->getIdBlockDb($block);

        if (!is_null($idBlockDb)) {
            $viewBlock->setIsPersisted(true);
            $viewBlock->setId($idBlockDb);
        }

        return $viewBlock;
    }

    public function createAll($blocks): array
    {
        $viewBlocks = [];

        foreach($blocks as $block) {
            $viewBlocks[] = $this->create($block);
        }

        return $viewBlocks;
    }

    private function getIdBlockDb($block): ?int
    {

        $blockDb = $this->blockRepository->findOneBy(['code' => $block['code']]);

        if (!is_null($blockDb)) {
            return $blockDb->getId();
        }

        return null;
    }


}
