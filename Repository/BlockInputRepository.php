<?php

namespace Aropixel\PageBundle\Repository;

use Aropixel\PageBundle\Entity\BlockInput;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BlockInput|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlockInput|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlockInput[]    findAll()
 * @method BlockInput[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlockInputRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlockInput::class);
    }

}
