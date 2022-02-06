<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class WeatherForecastsType
 * @package App\Form
 *
 * It is the form used to get what the city for which the user wants to visualize the weather forecasts.
 */
class WeatherForecastsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('city', ChoiceType::class, [
                'choices' => [
                    '' => 'null',
                    'Adilabad' => 'Adilabad',
                    'Hyderabad' => 'Hyderabad',
                    'Warangal' => 'Warangal',
                    'Nizamabad' => 'Nizamabad',
                    'Khammam' => 'Khammam',
                    'Karimnagar' => 'Karimnagar',
                    'Ramagundam' => 'Ramagundam',
                    'Mahabubnagar' => 'Mahabubnagar',
                    'Nalgonda' => 'Nalgonda',
                    'Suryapet' => 'Suryapet',
                    'Siddipet' => 'Siddipet',
                    'Jagtial' => 'Jagtial',
                ],
            ]);

        $builder->add('search', SubmitType::class, ['label' => 'Go']);
    }
}