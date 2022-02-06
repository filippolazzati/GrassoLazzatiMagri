<?php

namespace App\Form\DailyPlan;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemoveVisitType extends \Symfony\Component\Form\AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('visitToRemove', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('visit', HiddenType::class, [
            'empty_data' => $options['visitToRemove']
        ])
            ->add('send', SubmitType::class, ['label' => 'Remove Visit']);
    }
}