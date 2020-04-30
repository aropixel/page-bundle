<?php

declare(strict_types=1);

namespace Aropixel\PageBundle\Block;


use Aropixel\PageBundle\Entity\Block;
use Aropixel\PageBundle\Entity\BlockInput;
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

    /**
     * @param $code
     *
     * @return array
     *
     * récupère un block dans les blocks configurés
     */
    public function getConfiguredBlockByCode($code): array
    {
        if (empty($this->_configuredBlock)) {
            $configuredBlocks       = $this->getConfiguredBlocks();
            $this->_configuredBlock = $configuredBlocks[ $code ];
        }
        return $this->_configuredBlock;
    }

    /**
     * @param $code
     *
     * @return Block|null
     */
    private function getDbBlock( $code )
    {
        if (empty($this->_dbBlock)) {
            $this->_dbBlock = $this->blockRepository->findOneBy( [ 'code' => $code ] );
        }

        return  $this->_dbBlock;
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

        return $input->getContent()['content'];
    }

    /*
     * supprime les blocks existants en bdd mais non existants en config
     */
    public function cleanDeletedBlocks(): void
    {
        $configuredBlocks = $this->getConfiguredBlocks();

        $dbBlocks = $this->blockRepository->findAll();

        foreach ($dbBlocks as $dbBlock) {
            $this->cleanDeletedBlock($dbBlock, $configuredBlocks);
        }
    }

    public function getConfiguredBlock($blockCode)
    {
        $configuredBlocks = $this->getConfiguredBlocks();

        $blockConfig = $configuredBlocks[$blockCode];

        return $blockConfig;
    }

    public function getConfiguredBlockInputs($blockCode)
    {
        $blockConfig = $this->getConfiguredBlock($blockCode);

        return $blockConfig['inputs'];
    }

    public function hasTabsInput($blockCode)
    {
        $inputs = $this->getConfiguredBlockInputs($blockCode);

        if (in_array(  self::TABS_KEY, array_column($inputs, self::TABS_TYPE_KEY))) {
            return true;
        }

        return false;
    }

    public function getConfiguredBlockInput($inputCode, $blockCode)
    {
        $inputsBlockConfig = $this->getConfiguredBlockInputs($blockCode);

        $input = $inputsBlockConfig[$inputCode];

        return $input;

    }

    /**
     * @param Block $dbBlock
     * @param $configuredBlocks
     *
     * supprime un block existant en bdd mais non existant en config
     */
    public function cleanDeletedBlock(Block $dbBlock, $configuredBlocks): void
    {
        if (!array_key_exists($dbBlock->getCode(), $configuredBlocks)) {
            $this->entityManager->remove($dbBlock);
            $this->entityManager->flush();
        }
    }

    /**
     * @param $code
     *
     * vérifie pour un block donné que tous ses inputs en bdd correspondent à des inpits en config
     * sinon supprime les inputs en bdd
     */
    public function cleanDeletedBlockInput($code): void
    {
        $dbBlock = $this->getDbBlock( $code );

        if (!is_null($dbBlock)) {
            $dbBlockInputs = $dbBlock->getInputs();

            $blockConfig = $this->getConfiguredBlock($dbBlock->getCode());

            $configuredBlocksInput = $blockConfig['inputs'];

            foreach ($dbBlockInputs as $dbBlockInput) {

                if (!array_key_exists($dbBlockInput->getCode(), $configuredBlocksInput)) {
                    $this->entityManager->remove($dbBlockInput);
                    $this->entityManager->flush();
                }
            }
        }

    }

    /**
     * @param $code
     *
     * enregistre un block + ses blocks inputs en bdd
     */
    public function persistBlock($code): Block
    {

        $configuredBlock = $this->getConfiguredBlockByCode($code);

        $page = $this->pageRepository->findOneBy( [ 'code' => $configuredBlock['page'] ] );

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
