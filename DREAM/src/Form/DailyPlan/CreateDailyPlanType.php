<?php

namespace App\Form\DailyPlan;

use App\Controller\DailyPlan\DailyPlanController;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;



class CreateDailyPlanType extends \Symfony\Component\Form\AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('maxVisits', 0);
        $resolver->setDefault('date', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('numberOfVisits', IntegerType::class, [
            'constraints' => [new Positive()],
            'attr' => [ 'max' => $options['maxVisits'] ]
        ])->add('date', HiddenType::class, [
            'data' => $options['date']
        ]);
    }

}