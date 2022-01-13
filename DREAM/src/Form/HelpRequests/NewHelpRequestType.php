<?php

namespace App\Form\HelpRequests;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewHelpRequestType extends \Symfony\Component\Form\AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('experts', null);
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('expert', ChoiceType::class, [
            'choices' => [$options['experts']],
            'choice_value' => 'id',
            'choice_label' => 'fullName',
            'expanded' => false,
            'multiple' => false,
        ]);
    }
}