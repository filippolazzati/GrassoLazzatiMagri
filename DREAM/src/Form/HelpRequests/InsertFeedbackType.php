<?php

namespace App\Form\HelpRequests;

use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class InsertFeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('feedback', TextareaType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 5, 'max' => 1500]),
            ],
            'attr' => [
                'rows' => 5,
            ]
        ])
            ->add('send', SubmitType::class, ['label' => 'Send']);
    }
}