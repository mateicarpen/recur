<?php

namespace App\Form;

use App\Entity\FrequencyUnit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ]);
    }
}