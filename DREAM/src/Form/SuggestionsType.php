<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Positive;

/**
 * Class SuggestionsType
 * @package App\Form
 *
 * It is the form used to get what kind of suggestion the user wants to receive.
 */
class SuggestionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $type = $builder->getData()['type'] ?? null;

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Fertilizer' => 'fertilizer',
                    'Crop' => 'crop',
                ],
            ])
            ->add('crop', ChoiceType::class, [
                'choices' => [
                    'Potatoes' => 'potatoes',
                    'Tomatoes' => 'tomatoes',
                    'Salad' => 'salad',
                    'Onions' => 'onions',
                    'Radishes' => 'radishes',
                    'Cucumber' => 'cucumber',
                    'Cauliflower' => 'cauliflower',
                ],
                'required' => $type === 'fertilizer',
            ])
            ->add('area', NumberType::class, [
                'constraints' => [new Positive(), new LessThanOrEqual(1000)],
                'required' => $type === 'crop',
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Go',
            ]);
    }
}
