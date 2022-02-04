<?php

namespace App\Form;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

class SerializedType extends HiddenType
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new CallbackTransformer(
            function ($data) use ($options) {
                return $this->serializer->serialize(
                    $data,
                    $options['serializer_format'],
                    $options['serializer_context'],
                );
            },
            function ($data) use ($options) {
                return $this->serializer->deserialize(
                    $data,
                    $options['class'],
                    $options['serializer_format'],
                    $options['serializer_context'],
                );
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'serializer_format' => 'json',
            'serializer_context' => [],
        ]);
        $resolver->setRequired('class');

        $resolver->setAllowedTypes('serializer_format', ['string']);
        $resolver->setAllowedTypes('serializer_context', ['null', 'array']);
        $resolver->setAllowedTypes('class', ['string']);
    }
}