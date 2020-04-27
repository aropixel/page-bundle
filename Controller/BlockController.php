<?php

declare( strict_types=1 );

namespace Aropixel\PageBundle\Controller;

use Aropixel\PageBundle\Block\BlockManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/block")
 */
class BlockController extends AbstractController
{
	/**
	 * @Route("/", name="block_index", methods={"GET"})
	 */
	public function index(BlockManager $blockManager): Response
	{
	    // supprime en bdd les blocs existants qui ne sont pas présents en config
        $blockManager->cleanDeletedBlocks();

	    $VMconfiguredBlocks = $blockManager->getVMConfiguredBlocks();

		return $this->render( '@AropixelPage/block/index.html.twig', [
            'vmConfiguredblocks' => $VMconfiguredBlocks
        ]);
	}

    /**
     * @Route("/edit/{code}", name="block_edit", methods={"GET"})
     */
    public function create(
        BlockManager $blockManager,
        $code
    ): Response
    {
        // si le block n'a pas été trouvé en config, on redirige vers la liste
        if (!$blockManager->isConfiguredBlock($code)) {
            return $this->redirectToRoute('block_index');
        }

        // on sauve en bdd tous les blocks de la config
        $blockManager->persistBlock($code);

        // on supprime en bdd tous les blocks input qui n'existent pas dans la config
        $blockManager->cleanDeletedBlockInput($code);

        dump('persisted'); die;

        // afficher le form des champs

    }

}
