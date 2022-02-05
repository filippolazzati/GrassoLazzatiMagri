<?php

namespace App\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EntityNormalizer implements DenormalizerInterface
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_numeric($data) && !$this->em->getMetadataFactory()->isTransient($type);
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        return $this->em->find($type, $data);
    }
}