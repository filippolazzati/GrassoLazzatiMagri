<?php

namespace App\Form\DailyPlan;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfirmDailyPlanType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('confirm', SubmitType::class, ['label' => 'Confirm Daily Plan']);
    }
}