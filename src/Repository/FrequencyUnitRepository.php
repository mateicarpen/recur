<?php

namespace App\Repository;

use App\Entity\FrequencyUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FrequencyUnit|null find($id, $lockMode = null, $lockVersion = null)
 * @method FrequencyUnit|null findOneBy(array $criteria, array $orderBy = null)
 * @method FrequencyUnit[]    findAll()
 * @method FrequencyUnit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FrequencyUnitRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FrequencyUnit::class);
    }

//    /**
//     * @return FrequencyUnit[] Returns an array of FrequencyUnit objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    public function findOneById($id): ?FrequencyUnit
    {
        return $this->createQueryBuilder('f')
            ->where('f.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
