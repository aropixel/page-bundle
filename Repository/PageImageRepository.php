<?php

namespace Aropixel\PageBundle\Repository;

use Aropixel\PageBundle\Entity\PageImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PageImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageImage[]    findAll()
 * @method PageImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageImageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PageImage::class);
    }

    // /**
    //  * @return PageImage[] Returns an array of PageImage objects
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
    public function findOneBySomeField($value): ?PageImage
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
