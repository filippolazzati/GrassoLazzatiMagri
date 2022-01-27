<?php

namespace App\Form\HelpRequests;

use App\Entity\Agronomist;
use App\Entity\User;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewHelpRequestType extends \Symfony\Component\Form\AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('experts', null);
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('receiver', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'fullName',
            'choices' => $options['experts'],
            'multiple' => false,
            'expanded' => true
        ])
            ->add('title', TextareaType::class, [
                'constraints' => [
                new NotBlank(),
                new Length(['min' => 5, 'max' => 50]),
                ],
            ])
            ->add('text', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5, 'max' => 1500]),
                ],
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('send', SubmitType::class, ['label' => 'Send Request']);
    }
}