<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\FrequencyUnitRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TasksController extends Controller
{
    public function index(TaskRepository $taskRepo)
    {
        $task = $taskRepo->findAll();

        return $this->render('tasks/index.html.twig', [
            'tasks' => $task,
        ]);
    }

    public function create(FrequencyUnitRepository $unitRepo)
    {
        $unitOptions = $unitRepo->findAll();

        return $this->render('tasks/create.html.twig', [
            'unitOptions' => $unitOptions
        ]);
    }

    public function store(Request $request, EntityManagerInterface $em, FrequencyUnitRepository $unitRepo)
    {
        // TODO: validation

        $frequencyUnit = $unitRepo->findOneById($request->get('frequencyUnit'));

        $task = new Task;
        $task->setName($request->get('name'));
        $task->setFrequencyUnit($frequencyUnit);
        $task->setFrequency($request->get('frequency'));
        $task->setStartDate(new \DateTime($request->get('startDate')));
        $task->setAdjustOnCompletion((bool)$request->get('adjustOnCompletion'));

        $task->setCreateDate(new \DateTime()); // TODO: move from here wtf
        $task->setUpdateDate(new \DateTime());

        $em->persist($task);
        $em->flush();

        return $this->redirectToRoute('tasks_index');
    }
}