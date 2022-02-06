<?php

namespace App\Form;

use App\Entity\ProductionData\FertilizingEntry;
use App\Entity\ProductionData\HarvestingEntry;
use App\Entity\ProductionData\PlantingSeedingEntry;
use App\Entity\ProductionData\ProductionDataEntry;
use App\Entity\ProductionData\WateringEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductionDataEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Planting / Seeding' => PlantingSeedingEntry::class,
                    'Fertilizing' => FertilizingEntry::class,
                    'Watering' => WateringEntry::class,
                    'Harvesting' => HarvestingEntry::class,
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductionDataEntry::class,
        ]);
    }
}
