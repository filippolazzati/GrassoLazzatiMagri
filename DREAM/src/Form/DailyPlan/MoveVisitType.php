<?php

namespace App\Form\DailyPlan;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveVisitType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('visitToMove', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('visit', HiddenType::class, [
                'data' => $options['visitToMove']
            ])
            ->add('newStartHour', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'choice',
                'hours' => range(8, 18),
            ])
            ->add('send', SubmitType::class, ['label' => 'Move Visit']);
    }
}