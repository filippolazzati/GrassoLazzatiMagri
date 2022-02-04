<?php

namespace App\Form\DailyPlan;

use App\Entity\Farm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddVisitType extends \Symfony\Component\Form\AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('farmsInTheArea', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('farm', EntityType::class, [
            'class' => Farm::class,
            'choice_label' => function ($farm) {
                return 'Farmer: ' . $farm->getFarmer()->getFullName() .
                    '     Address: ' . $farm->getCity() . ' ' . $farm->getStreet();
            },
            'choices' => $options['farmsInTheArea'],
            'multiple' => false,
            'expanded' => true
        ])
            ->add('startingHour', TimeType::class, [
            'input' => 'datetime',
            'widget' => 'choice',
            'hours' => [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18]
        ])
            ->add('send', SubmitType::class, ['label' => 'Add Visit']);
    }
}