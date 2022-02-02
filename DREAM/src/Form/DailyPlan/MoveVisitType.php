<?php

namespace App\Form\DailyPlan;

use Doctrine\DBAL\Types\TimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class MoveVisitType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('newStartHour', TimeType::class, [
            'input' => 'datetime',
            'widget' => 'choice',
            'hours' => [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18]
        ])
            ->add('send', SubmitType::class, ['label' => 'Move Visit']);;
    }
}