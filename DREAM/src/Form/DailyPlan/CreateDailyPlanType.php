<?php

namespace App\Form\DailyPlan;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;


class CreateDailyPlanType extends \Symfony\Component\Form\AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('maxVisits', 0);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('numberOfVisits', IntegerType::class, [
            'constraints' => [new Positive()],
            'attr' => [ 'max' => $options['maxVisits'] ]
        ])
        ->add('create', SubmitType::class, ['label' => 'Generate Daily Plan']);
    }

}