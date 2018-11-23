<?php

namespace App\Services;

use App\Entity\FrequencyUnit;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use DateTime;

class TodoPlanner
{
    /**
     * @var TaskRepository
     */
    private $taskRepo;


    public function __construct(TaskRepository $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }


    /**
     * @return Task[]
     */
    public function getTasksDueToday(User $user): array
    {
        $today = new DateTime('today');

        return $this->getTasksDue($today, $user);
    }


    /**
     * @param Task[] $tasksDueToday
     * @return Task[]
     */
    public function getTasksDueTomorrow(array $tasksDueToday, User $user): array
    {
        $tomorrow = new DateTime('tomorrow');

        $tasks = $this->getTasksDue($tomorrow, $user);
        $tasks = $this->excludeTasks($tasks, $tasksDueToday);

        return $tasks;
    }


    /**
     * @param Task[] $tasksDueToday
     * @param Task[] $tasksDueTomorrow
     * @return Task[]
     */
    public function getTasksDueNextDays(array $tasksDueToday, array $tasksDueTomorrow, User $user): array
    {
        $date = new DateTime('today + 6 days');

        $tasks = $this->getTasksDue($date, $user);
        $tasks = $this->excludeTasks($tasks, array_merge($tasksDueToday, $tasksDueTomorrow));

        return $tasks;
    }


    /**
     * @return Task[]
     */
    private function getTasksDue(DateTime $date, User $user): array
    {
        $tasks = $this->taskRepo->findStartedEarlierThan($date, $user);

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


    private function getTaskDueDate(Task $task): DateTime
    {
        $interval = $this->getTaskInterval($task);

        $adjustOnCompletion = $task->getAdjustOnCompletion();
        if ($adjustOnCompletion) {
            $date = clone $task->getLastCompleted();

            return $date->add($interval);
        } else {
            $currentDate = new DateTime('today');
            $date = clone $task->getStartDate();

            while($date < $currentDate) {
                $date->add($interval);
            }

            return $date;
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