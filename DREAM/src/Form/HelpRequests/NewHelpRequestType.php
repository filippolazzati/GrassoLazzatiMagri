<?php

namespace App\Form\HelpRequests;

use App\Entity\Agronomist;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('title', TextType::class)
            ->add('text', TextType::class);
    }
}