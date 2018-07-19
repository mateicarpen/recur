<?php

namespace App\Services;

use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use App\Repository\TaskLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TaskManager
{
    /**
     * @var User
     */
    private $currentUser;

    /**
     * @var EntityManagerInterface
     */
    private $em;


    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $em)
    {
        $this->currentUser = $tokenStorage->getToken()->getUser();
        $this->em = $em;
    }


    public function store(Task $task): void
    {
        $task->setUser($this->currentUser);
        $task->setCreateDate(new \DateTime());
        $task->setUpdateDate(new \DateTime());

        $this->em->persist($task);
        $this->em->flush();
    }


    public function update(Task $task): void
    {
        $task->setUpdateDate(new \DateTime());

        $this->em->flush();
    }


    public function delete(Task $task): void
    {
        $this->em->remove($task);
        $this->em->flush();
    }


    public function complete(Task $task): void
    {
        $now = new \DateTime();
        $task->setLastCompleted($now);

        $log = new TaskLog();
        $log->setTask($task);
        $log->setCreateDate($now);

        $this->em->persist($log);
        $this->em->flush();
    }

    public function undoCompletion(Task $task, TaskLogRepository $logRepo)
    {
        $fiveMinutesAgo = new \DateTime('-5 minutes');
        if (is_null($task->getLastCompleted()) || $task->getLastCompleted() < $fiveMinutesAgo) {
            return;
        }

        $taskLogs = $logRepo->findByTask($task, 2);

        $this->em->remove($taskLogs[0]); // remove freshly inserted log

        if (!empty($taskLogs[1])) {
            $task->setLastCompleted($taskLogs[1]->getCreateDate());
        } else {
            $task->setLastCompleted(null);
        }

        $this->em->flush();
    }
}