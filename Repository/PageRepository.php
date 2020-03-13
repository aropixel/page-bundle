<?php

namespace Aropixel\PageBundle\Repository;

use Aropixel\AdminBundle\Repository\PublishableRepository;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends PublishableRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Page::class);
    }

    // /**
    //  * @return Page[] Returns an array of Page objects
    //  */
    public function findNotPreset()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPresetPage = :preset')
            ->orWhere('p.isPresetPage IS NULL')
            ->setParameter('preset', false)
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult()
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
