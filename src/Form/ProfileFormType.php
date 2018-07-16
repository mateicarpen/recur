<?php

namespace App\Form;

use App\Entity\User;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfileFormType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileFormType extends BaseProfileFormType
{
    public function __construct()
    {
        parent::__construct(User::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name')
            ->remove('username')
            ->remove('email');
    }
}