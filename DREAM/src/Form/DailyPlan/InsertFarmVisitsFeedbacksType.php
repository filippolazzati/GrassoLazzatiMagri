<?php

namespace App\Form\DailyPlan;

use App\Entity\DailyPlan\FarmVisit;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class InsertFarmVisitsFeedbacksType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('farmVisits', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ArrayCollection $farmVisits */
        $farmVisits = $options['farmVisits'];

        /** @var FarmVisit $farmVisit */
        foreach ($farmVisits as $farmVisit) {
            $builder->add($farmVisit->getId(),
                TextareaType::class, [
                    'constraints' => [
                        new NotBlank(),
                        new Length(['min' => 5, 'max' => 1000]),
                    ],
                    'attr' => [
                        'rows' => 5,
                    ]
                ]);
        }

        $builder->add('insert', SubmitType::class, ['label' => 'Insert Feedbacks']);
    }
}