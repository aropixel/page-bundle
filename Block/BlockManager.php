<?php

declare(strict_types=1);

namespace Aropixel\PageBundle\Block;


use Aropixel\PageBundle\Entity\Block;
use Aropixel\PageBundle\Entity\BlockInput;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Repository\BlockInputRepository;
use Aropixel\PageBundle\Repository\BlockRepository;
use Aropixel\PageBundle\Repository\PageRepository;
use Aropixel\PageBundle\ViewModel\ViewBlockConfigAssembler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BlockManager
{
    private const TABS_KEY = 'Tabs';
    private const TABS_TYPE_KEY = 'type';

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var ViewBlockConfigAssembler
     */
    private $viewBlockConfigAssembler;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var BlockInputRepository
     */
    private $blockInputRepository;

    private $_configuredBlocks = [];

    private $_dbBlock;

    private $_configuredBlock;

    public function __construct(
        BlockRepository $blockRepository,
        ParameterBagInterface $params,
        ViewBlockConfigAssembler $viewBlockConfigAssembler,
        PageRepository $pageRepository,
        EntityManagerInterface $entityManager,
        BlockInputRepository $blockInputRepository
    )
    {
        $this->blockRepository = $blockRepository;
        $this->params = $params;
        $this->viewBlockConfigAssembler = $viewBlockConfigAssembler;
        $this->pageRepository = $pageRepository;
        $this->entityManager = $entityManager;
        $this->blockInputRepository = $blockInputRepository;
    }


    /**
     * @return array
     *
     * Créé les view models des blocks à envoyer dans le twig de liste des blocks
     */
    public function getVMConfiguredBlocks(): array
    {
        $configuredBlocks = $this->getConfiguredBlocks();

        $viewModelBlocks = $this->viewBlockConfigAssembler->createAll($configuredBlocks);

        return $viewModelBlocks;
    }

    /**
     * @return mixed
     *
     * récupère tous les blocks configurés
     */
    private function getConfiguredBlocks(): array
    {
        if (empty($this->_configuredBlocks)) {
            $this->_configuredBlocks = $this->params->get('aropixel_page.blocks');
        }

        return $this->_configuredBlocks;
    }


    private function getConfiguredBlocksByPage(Page $page): array
    {
        $configuredBlocks = $this->getConfiguredBlocks();

        $pageCode = $page->getCode();

        $pageBlocks = [];

        foreach ($configuredBlocks as $blockCode => $block) {
            if ($block['page'] === $pageCode) {
                $pageBlocks[$blockCode] = $block;
            }
        }

        return $pageBlocks;
    }

    /**
     * @param $code
     *
     * @return array
     *
     * récupère un block dans les blocks configurés
     */
    public function getConfiguredBlockByCode($code): array
    {
        $configuredBlocks = $this->getConfiguredBlocks();
        return  $configuredBlocks[ $code ];
    }


    public function getConfiguredBlockInputs($blockCode)
    {
        $blockConfig = $this->getConfiguredBlockByCode($blockCode);

        return $blockConfig['inputs'];
    }

    /**
     * @param $code
     *
     * @return bool
     *
     * vérifie si un block existe dans les blocks configurés
     */
    public function isConfiguredBlock($code): bool
    {
        return array_key_exists($code, $this->getConfiguredBlocks());
    }

    public function getConfiguredBlockInput($inputCode, $blockCode)
    {
        $inputsBlockConfig = $this->getConfiguredBlockInputs($blockCode);

        $input = $inputsBlockConfig[$inputCode];

        return $input;
    }

    public function hasConfiguredTabsInput($blockCode)
    {
        $inputs = $this->getConfiguredBlockInputs($blockCode);

        if (in_array(  self::TABS_KEY, array_column($inputs, self::TABS_TYPE_KEY))) {
            return true;
        }

        return false;
    }

    /**
     * @param $code
     *
     * @return Block|null
     */
    private function getDbBlock( $code )
    {
        return  $this->blockRepository->findOneBy( [ 'code' => $code ] );
    }

    /**
     * @param $blockInputCode
     * @param $blockEntity
     *
     * @return BlockInput|null
     *
     * recupère en bdd un block input en fonction de son code et du block relié
     */
    public function getDbBlockInput($blockInputCode)
    {
        $input = $this->blockInputRepository->findOneBy([
            'code'=> $blockInputCode
        ]);

        return $input;
    }

    /*
     * supprime les blocks existants en bdd mais non existants en config
     */
    public function cleanDeletedBlocks(): void
    {

        $dbBlocks = $this->blockRepository->findAll();

        foreach ($dbBlocks as $dbBlock) {
            $this->cleanDeletedBlock($dbBlock);
        }
    }

    public function cleanDeletedBlocksByPage(Page $page)
    {
        $dbBlocks = $this->blockRepository->findBy(['page' => $page]);

        foreach ($dbBlocks as $dbBlock) {
            $this->cleanDeletedBlock($dbBlock);
        }
    }

    public function cleanDeletedBlockInputsByBlockCode($code)
    {
        $dbBlock = $this->getDbBlock( $code );

        // si le bloc en bdd existe, on clean ses inputs
        if (!is_null($dbBlock)) {
            $this->cleanDeletedInputsByBlock($dbBlock);
        }

        $this->entityManager->flush();
    }


    /**
     * @param Block $dbBlock
     * @param $configuredBlocks
     *
     * supprime un block existant en bdd mais non existant en config
     */
    public function cleanDeletedBlock(Block $dbBlock): void
    {
        // si le bloc n'existe plus en config on le supprime (par cascade ça supprime aussi les inputs liés) de la bdd
        if (!array_key_exists($dbBlock->getCode(), $this->getConfiguredBlocks())) {
            $this->entityManager->remove($dbBlock);
        // si le bloc est toujours valide, on vérifie et supprime les inputs s'ils sont invalides
        } else {
            $this->cleanDeletedInputsByBlock($dbBlock);
        }

        $this->entityManager->flush();
    }

    /**
     * @param $code
     *
     * vérifie pour un block donné que tous ses inputs en bdd correspondent à des inpits en config
     * sinon supprime les inputs en bdd
     */
    public function cleanDeletedInputsByBlock(Block $dbBlock): void
    {

        if (!is_null($dbBlock)) {
            $dbBlockInputs = $dbBlock->getInputs();

            $configuredBlocksInputs = $this->getConfiguredBlockInputs($dbBlock->getCode());

            foreach ($dbBlockInputs as $dbBlockInput) {

                // on supprime les blocsinput non valides :
                // si l'input existe en bdd mais plus en config ou si le type a été modifié en config
                if ($this->isBlockInputInvalid($dbBlockInput, $configuredBlocksInputs)) {
                    $this->entityManager->remove($dbBlockInput);
                }

            }
        }
    }

    /**
     * @param $dbBlockInput
     * @param $configuredBlocksInput
     *
     * @return bool
     *
     * vérifie si un input est présent en bdd alors qu'il a été supprimé de la config
     * ou si le type en bdd ne correspond plus à celui de la config
     */
    private function isBlockInputInvalid(BlockInput $dbBlockInput, $configuredBlocksInput): bool
    {
        $dbCodeBlockInput = $dbBlockInput->getCode();
        $dbtypeBlockInput = $dbBlockInput->getType();

        $isBlockInputInvalid = (!array_key_exists($dbCodeBlockInput, $configuredBlocksInput))
                               || ($dbtypeBlockInput!== $configuredBlocksInput[$dbCodeBlockInput]['type']);

        return $isBlockInputInvalid;
    }

    public function persistBlocksByPage($page)
    {
        // je récupère en config tous les blocks liés à cette page
        $pageBlocks = $this->getConfiguredBlocksByPage($page);

        // je les persists avec persist block
        foreach ($pageBlocks as $blockCode => $block) {
            $this->persistBlock($blockCode, $page);
        }

    }

    /**
     * @param $code
     *
     * enregistre un block + ses blocks inputs en bdd
     */
    public function persistBlock($code, $page = null): Block
    {
        $configuredBlock = $this->getConfiguredBlockByCode($code);

        if (is_null($page)) {
            $page = $this->pageRepository->findOneBy( [ 'code' => $configuredBlock['page'] ] );
        }

        $dbBlock = $this->getDbBlock( $code );

        // si le block n'existe pas en bdd, on le créé
        if (is_null($dbBlock)) {
            $dbBlock = new Block();
            $dbBlock->setCode($configuredBlock['code']);
            $dbBlock->setPage($page);

            $this->entityManager->persist($dbBlock);
        }

        // enregistre les blocs inputs d'un bloc en bdd
        $this->persistBlockInput( $configuredBlock, $dbBlock );

        $this->entityManager->flush();

        return $dbBlock;

    }

    /**
     * @param $block
     * @param Block $blockEntity
     *
     * Enregistre un bloc input en bdd
     */
    private function persistBlockInput( $block, Block $blockEntity ): void
    {
        foreach ( $block['inputs'] as $blockInputCode => $blockInput ) {

            $dbBlockInput = $this->getDbBlockInput($blockInputCode);

            if (is_null($dbBlockInput)) {
                $blockInputEntity = new BlockInput();

                $blockInputEntity->setCode( $blockInputCode );
                $blockInputEntity->setBlock( $blockEntity );
                $blockInputEntity->setType( $blockInput['type'] );
                $blockEntity->addInput($blockInputEntity);

                $this->entityManager->persist( $blockInputEntity );
            }

        }
    }

}
