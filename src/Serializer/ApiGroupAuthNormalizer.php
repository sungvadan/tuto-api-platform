<?php

namespace App\Serializer;

use App\Attribute\ApiGroupAuth;
use App\Entity\User;
use App\Entity\UserOwnedInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ApiGroupAuthNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALL_READY_NORMALIZE = 'ALL_READY_NORMALIZE';

    public function __construct(
      private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        if (!is_object($data)) {
            return false;
        }
        $reflectionClass = new \ReflectionClass(get_class($data));
        $attributes = $reflectionClass->getAttributes(ApiGroupAuth::class);
        $allReadyDenormalize = $context[self::ALL_READY_NORMALIZE] ?? false;
        return $allReadyDenormalize === false && !empty($attributes);    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $context[self::ALL_READY_NORMALIZE] = true;
        $reflectionClass = new \ReflectionClass(get_class($object));
        /** @var ApiGroupAuth $apiGroupAuth */
        $apiGroupAuth = $reflectionClass->getAttributes(ApiGroupAuth::class)[0]->newInstance();
        foreach ($apiGroupAuth->groups as $role => $group) {
            if ($this->authorizationChecker->isGranted($role, $object)) {
                $context['groups'] = array_merge((array) $context['groups'] ?? [], $group);
            }
        }

        return $this->normalizer->normalize($object, $format, $context);
    }
}