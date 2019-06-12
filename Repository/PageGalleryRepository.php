<?php

namespace Aropixel\PageBundle\Repository;

use Aropixel\PageBundle\Entity\PageGallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PageGallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageGallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageGallery[]    findAll()
 * @method PageGallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageGalleryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PageGallery::class);
    }

    // /**
    //  * @return PageGallery[] Returns an array of PageGallery objects
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
    public function findOneBySomeField($value): ?PageGallery
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
