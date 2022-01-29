<?php

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

class SuggestionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type',  ChoiceType::class, [
                'choices'  => [
                    'Fertilizer' => true, # it returns true if fertilizer is selected
                    'Crop' => false,
                ],
            ])
            ->add('data', TextType::class);

        $builder->add('search', SubmitType::class, ['label' => 'Go']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Controller\suggestions\SuggestionChoice::class,
        ]);
    }
}
