<?php

namespace Aropixel\PageBundle\Repository;

use Aropixel\PageBundle\Entity\PageImageCrop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PageImageCrop|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageImageCrop|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageImageCrop[]    findAll()
 * @method PageImageCrop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageImageCropRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageImageCrop::class);
    }

    // /**
    //  * @return PageImageCrop[] Returns an array of PageImageCrop objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PageImageCrop
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
