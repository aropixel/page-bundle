<?php

namespace Aropixel\PageBundle\Repository;

use Aropixel\AdminBundle\Repository\PublishableRepository;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
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
     * @param $type
     * @return Page Returns an array of Page objects
     */
    public function findPage($type)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    // /**
    //  * @return Page[] Returns an array of Page objects
    //  */
    public function findNotPresetAndPublished()
    {
        return $this->qbPublished('p')
            ->andWhere('p.isPresetPage = :preset')
            ->orWhere('p.isPresetPage IS NULL')
            ->setParameter('preset', false)
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }


    /*
    public function findOneBySomeField($value): ?Page
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
