<?php

namespace App\Controller;

use App\Entity\FrequencyUnit;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Repository\FrequencyUnitRepository;
use App\Repository\TaskLogRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class TasksController extends Controller
{
    public function todo(TaskRepository $taskRepo)
    {
        // TODO: ma asigur ca nu o sa fie probleme cu datele (sa fie toate despre ora 00 - cred)

        $tasks = $taskRepo->findStartedEarlierThan(new \DateTime, $this->getUser());
        $currentDate = new \DateTime;

        $todos = [];
        foreach ($tasks as $task) {
            /** @var $task Task */

            if (is_null($task->getLastCompleted())) {
                $todos[] = $task;
                continue;
            }

            $dueDate = $this->getTaskDueDate($task);

            if ($dueDate <= $currentDate && $task->getLastCompleted() < $dueDate) {
                $todos[] = $task;
            }
        }

        return $this->render('tasks/todos.html.twig', [
            'todos' => $todos,
        ]);
    }

    public function complete($id, TaskRepository $taskRepo, EntityManagerInterface $em)
    {
        $task = $taskRepo->findByUser($id, $this->getUser()); // exception if not found

        $now = new \DateTime();
        $task->setLastCompleted($now);

        $log = new TaskLog();
        $log->setTask($task);
        $log->setCreateDate($now);

        $em->persist($log);
        $em->flush();

        return $this->redirectToRoute('todo');
    }

    public function index(TaskRepository $taskRepo)
    {
        $task = $taskRepo->findAllByUser($this->getUser());

        return $this->render('tasks/index.html.twig', [
            'tasks' => $task,
        ]);
    }

    public function logs(int $id, TaskRepository $taskRepo, TaskLogRepository $logRepo)
    {
        $task = $taskRepo->findByUser($id, $this->getUser()); // exception if not found
        $logs = $logRepo->findByTask($task);

        return $this->render('tasks/logs.html.twig', [
            'task' => $task,
            'logs' => $logs,
        ]);
    }

    public function create(Request $request, EntityManagerInterface $em, FrequencyUnitRepository $unitRepo)
    {
        $task = new Task;
        $form = $this->getForm($task);
        $form->handleRequest($request);

        // validation
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $task->setUser($this->getUser());
            $task->setCreateDate(new \DateTime()); // TODO: move from here wtf
            $task->setUpdateDate(new \DateTime());

            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('tasks_index');
        }

        return $this->render('tasks/form.html.twig', [
            'title' => 'Create task',
            'form' => $form->createView()
        ]);
    }

    public function edit(int $id, Request $request, EntityManagerInterface $em, TaskRepository $taskRepo, FrequencyUnitRepository $unitRepo)
    {
        $task = $taskRepo->findByUser($id, $this->getUser());

        $form = $this->getForm($task);
        $form->handleRequest($request);

        // validation
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $task->setUpdateDate(new \DateTime());

            $em->flush();

            return $this->redirectToRoute('tasks_index');
        }

        return $this->render('tasks/form.html.twig', [
            'title' => 'Edit task',
            'form' => $form->createView(),
        ]);
    }

    public function delete(int $id, TaskRepository $taskRepo, EntityManagerInterface $em)
    {
        $task = $taskRepo->findByUser($id, $this->getUser());

        $em->remove($task);
        $em->flush();

        return $this->redirectToRoute('tasks_index');
    }

    private function getForm(Task $task = null)
    {
        return $this->createFormBuilder($task, [
            'method' => 'POST'
        ])
        ->add('name', TextType::class)
        ->add('frequency', IntegerType::class)
        ->add('frequencyUnit', EntityType::class, [
            'class' => FrequencyUnit::class,
            'choice_label' => 'name'
        ])
        ->add('startDate', DateType::class, [
            'widget' => 'single_text',
        ])
        ->add('adjustOnCompletion', ChoiceType::class, [
            'choices' => [
                'Yes' => true,
                'No' => false
            ],
            'expanded' => true,
        ])
        ->getForm();
    }

    private function getTaskDueDate(Task $task)
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

        $interval = new \DateInterval('P' . $task->getFrequency() . $periodString);

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
}