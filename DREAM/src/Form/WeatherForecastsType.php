<?php


namespace App\Form;


use App\Controller\forecasts\ForecastsChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeatherForecastsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('city',  ChoiceType::class, [
                'choices'  => [
                    'Hyderabad' => 'Hyderabad', // return 'Hyderabad' if 'Hyderabad' is selected
                    'Warangal' => 'Warangal',
                    'Nizamabad' => 'Nizamabad',
                    'Khammam' => 'Khammam',
                    'Karimnagar' => 'Karimnagar',
                    'Ramagundam' => 'Ramagundam',
                    'Mahabubnagar' => 'Mahabubnagar',
                    'Nalgonda' => 'Nalgonda',
                    'Adilabad' => 'Adilabad',
                    'Suryapet' => 'Suryapet',
                    'Siddipet' => 'Siddipet',
                    'Jagtial' => 'Jagtial',
                ],
            ]);

        $builder->add('search', SubmitType::class, ['label' => 'Go']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ForecastsChoice::class,
        ]);
    }
}