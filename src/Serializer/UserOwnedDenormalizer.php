<?php

namespace App\Serializer;

use App\Entity\User;
use App\Entity\UserOwnedInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class UserOwnedDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALL_READY_DENORMALIZE = 'ALL_READY_DENORMALIZE';

    public function __construct(
      private Security $security
    ) {
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return false;
        $reflectionClass = new \ReflectionClass($type);
        $allReadyDenormalize = $data[self::ALL_READY_DENORMALIZE] ?? false;
        return $reflectionClass->implementsInterface(UserOwnedInterface::class) && $allReadyDenormalize === false;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $data[self::ALL_READY_DENORMALIZE] = true;
        /** @var UserOwnedInterface $obj */
        $obj = $this->denormalizer->denormalize($data, $type, $format, $context);
        /** @var User $user */
        $user = $this->security->getUser();
        $obj->setUser($user);

        return $obj;
    }
}