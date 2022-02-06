<?php

namespace App\Form;

use App\Entity\ProductionData\ProductionData;
use App\Entity\ProductionData\ProductionDataEntry;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductionDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'data' => new DateTime(),
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
            ->add('entries', SerializedType::class, [
                'class' => ProductionDataEntry::class . '[]',
                'serializer_context' => ['groups' => ['form']],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductionData::class,
        ]);
    }
}
