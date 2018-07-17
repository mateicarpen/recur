<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findAllByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    public function findByUser(int $id, User $user): ?Task
    {
        return $this->findOneBy([
            'id'   => $id,
            'user' => $user,
        ]);
    }

    public function findStartedEarlierThan(\DateTime $date, User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.startDate <= :date')
            ->andWhere('t.user = :user')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
