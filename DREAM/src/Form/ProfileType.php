<?php

namespace App\Form;

use App\Entity\Agronomist;
use App\Entity\Area;
use App\Entity\Farmer;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('name', TextType::class)
            ->add('surname', TextType::class)
            ->add('birthDate', DateType::class, [
                'widget' => 'single_text',
            ]);

        $entity = $builder->getData();
        if ($entity instanceof Farmer) {
            $builder
                ->add('farmArea', EntityType::class, [
                    'class' => Area::class,
                    'mapped' => false,
                    'data' => $builder->getData()?->getFarm()?->getArea(),
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
                    'data' => $builder->getData()?->getFarm()?->getCity(),
                ])
                ->add('farmStreet', TextType::class, [
                    'required' => false,
                    'mapped' => false,
                    'data' => $builder->getData()?->getFarm()?->getStreet(),
                ]);
        }
        if ($entity instanceof Agronomist) {
            $builder
                ->add('area', EntityType::class, [
                    'class' => Area::class
                ]);
        }

        $builder->add('submit', SubmitType::class, ['label' => 'Save']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
