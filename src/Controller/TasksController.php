<?php

namespace App\Controller;

use App\Entity\FrequencyUnit;
use App\Entity\Task;
use App\Repository\FrequencyUnitRepository;
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
    public function index(TaskRepository $taskRepo)
    {
        $task = $taskRepo->findAll();

        return $this->render('tasks/index.html.twig', [
            'tasks' => $task,
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

    public function edit($id, Request $request, EntityManagerInterface $em, TaskRepository $taskRepo, FrequencyUnitRepository $unitRepo)
    {
        $task = $taskRepo->find($id);

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

    public function delete($id, TaskRepository $taskRepo, EntityManagerInterface $em)
    {
        $task = $taskRepo->find($id);

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
}