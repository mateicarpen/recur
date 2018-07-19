<?php

namespace App\Services;

use App\Entity\FrequencyUnit;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TodoPlanner
{
    /**
     * @var TaskRepository
     */
    private $taskRepo;

    /**
     * @var User
     */
    private $currentUser;


    public function __construct(TaskRepository $taskRepo, TokenStorageInterface $tokenStorage)
    {
        $this->taskRepo = $taskRepo;
        $this->currentUser = $tokenStorage->getToken()->getUser();
    }


    /**
     * @return Task[]
     */
    public function getTasksDueToday(): array
    {
        $today = new \DateTime;

        return $this->getTasksDue($today);
    }


    /**
     * @param Task[] $tasksDueToday
     * @return Task[]
     */
    public function getTasksDueTomorrow(array $tasksDueToday): array
    {
        $tomorrow = new \DateTime('+1 day');

        $tasks = $this->getTasksDue($tomorrow);
        $tasks = $this->excludeTasks($tasks, $tasksDueToday);

        return $tasks;
    }


    /**
     * @param Task[] $tasksDueToday
     * @param Task[] $tasksDueTomorrow
     * @return Task[]
     */
    public function getTasksDueNextDays(array $tasksDueToday, array $tasksDueTomorrow): array
    {
        $date = new \DateTime('+6 days');

        $tasks = $this->getTasksDue($date);
        $tasks = $this->excludeTasks($tasks, array_merge($tasksDueToday, $tasksDueTomorrow));

        return $tasks;
    }


    /**
     * @param \DateTime $date
     * @return Task[]
     */
    private function getTasksDue(\DateTime $date): array
    {
        $tasks = $this->taskRepo->findStartedEarlierThan($date, $this->currentUser);

        $dueTasks = [];
        foreach ($tasks as $task) {
            /** @var $task Task */

            if (is_null($task->getLastCompleted())) {
                $dueTasks[] = $task;
                continue;
            }

            $dueDate = $this->getTaskDueDate($task);

            if ($dueDate <= $date && $task->getLastCompleted() < $dueDate) {
                $dueTasks[] = $task;
            }
        }

        return $dueTasks;
    }


    private function getTaskDueDate(Task $task): \DateTime
    {
        $interval = $this->getTaskInterval($task);

        $adjustOnCompletion = $task->getAdjustOnCompletion();
        if ($adjustOnCompletion) {
            $date = clone $task->getLastCompleted();

            return $date->add($interval);
        } else {
            $date = clone $task->getStartDate();
            $currentDate = new \DateTime('today');

            while($date < $currentDate) {
                $dueDate = clone $date;

                $date->add($interval);
            }

            return $dueDate;
        }

    }


    /**
     * @param Task[] $tasks
     * @param Task[] $tasksToExclude
     * @return Task[]
     */
    private function excludeTasks(array $tasks, array $tasksToExclude): array
    {
        foreach ($tasks as $key => $task) {
            if (in_array($task, $tasksToExclude)) {
                unset($tasks[$key]);
            }
        }

        return $tasks;
    }


    private function getTaskInterval(Task $task): \DateInterval
    {
        switch ($task->getFrequencyUnit()->getId()) {
            case FrequencyUnit::DAY:
                $periodString = 'D';
                break;

            case FrequencyUnit::WEEK:
                $periodString = 'W';
                break;

            case FrequencyUnit::MONTH:
                $periodString = 'M';
                break;
        }

        return new \DateInterval('P' . $task->getFrequency() . $periodString);
    }
}