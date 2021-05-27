<?php

namespace App\Serializer;

use App\Entity\Post;
use App\Entity\UserOwnedInterface;
use App\Security\Voter\UserOwnedVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class UserOwnedNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_NORMALIZE =  'ALREADY_NORMLIZE';

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        $alreadyNormalize = $context[self::ALREADY_NORMALIZE] ?? false;
        return $data instanceof UserOwnedInterface && $alreadyNormalize === false;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $context[self::ALREADY_NORMALIZE] = true;
        if (
            $this->authorizationChecker->isGranted(UserOwnedVoter::CAN_EDIT, $object) &&
            isset($context['groups'])
        ) {
            $context['groups'] = array_merge((array) $context['groups'], ['read:collection:User']);
        }

        return $this->normalizer->normalize($object, $format, $context);

    }
}