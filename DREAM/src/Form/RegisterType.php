<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\Farmer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Service\Attribute\Required;

class RegisterType extends AbstractType
{
    #[Required] public UserPasswordHasherInterface $passwordHasher;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('surname', TextType::class)
            ->add('birthDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('email', EmailType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'constraints' => [
                    new Length(['min' => 8, 'max' => 4096]),
                ],
                'mapped' => false,
            ])
            ->add('farmArea', EntityType::class, [
                'class' => Area::class,
                'mapped' => false
            ])
            ->add('farmCity', ChoiceType::class, [
                'choices' => [
                    'Hyderabad' => 'Hyderabad',
                    'Warangal' => 'Warangal',
                    'Nizamabad' => 'Nizamabad',
                    'Khammam' => 'Khammam',
                    'Karimnagar' => 'Karimnagar',
                    'Ramagundam' => 'Ramagundam',
                    'Mahabubnagar' => 'Mahabubnagar',
                    'Adilabad' => 'Adilabad',
                    'Suryapet' => 'Suryapet',
                    'Siddipet' => 'Siddipet',
                    'Nalgonda' => 'Nalgonda',
                    'Jagtial' => 'Jagtial',
                ],
                'mapped' => false,
            ])
            ->add('farmStreet', TextType::class, ['required' => false, 'mapped' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Farmer::class,
        ]);
    }
}
