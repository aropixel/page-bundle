<?php

namespace Aropixel\PageBundle\Repository;

use Aropixel\PageBundle\Entity\PageGalleryCrop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PageGalleryCrop|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageGalleryCrop|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageGalleryCrop[]    findAll()
 * @method PageGalleryCrop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageGalleryCropRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PageGalleryCrop::class);
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
