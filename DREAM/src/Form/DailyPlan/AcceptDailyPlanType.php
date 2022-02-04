<?php

namespace App\Form\DailyPlan;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class AcceptDailyPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('accept', SubmitType::class, ['label' => 'Accept Daily Plan']);
    }
}