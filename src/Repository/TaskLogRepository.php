<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\TaskLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TaskLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskLog[]    findAll()
 * @method TaskLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TaskLog::class);
    }

    public function findByTask(Task $task): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.task=:task')
            ->setParameter('task', $task)
            ->orderBy('t.create_date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
