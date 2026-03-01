<?php

namespace Aropixel\PageBundle\Repository;

use Aropixel\AdminBundle\Repository\PublishableRepository;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends PublishableRepository
{
    public function __construct(ManagerRegistry $registry, ParameterBagInterface $parameterBag)
    {
        $entities = $parameterBag->get('aropixel_page.entities');
        parent::__construct($registry, $entities[PageInterface::class]);
    }

    /**
     * @return Page[]
     */
    public function findDefaultPages(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.type = :type')
            ->setParameter('type', Page::TYPE_DEFAULT)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Page[]
     */
    public function findCustomPages(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.type = :type')
            ->setParameter('type', Page::TYPE_CUSTOM)
            ->getQuery()
            ->getResult()
        ;
    }

    public function add(Page $page, bool $flush = false): void
    {
        $this->getEntityManager()->persist($page);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Page $page, bool $flush = false): void
    {
        $this->getEntityManager()->remove($page);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
