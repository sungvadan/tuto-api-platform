<?php


namespace App\Serializer;


use App\Entity\Post;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Vich\UploaderBundle\Storage\StorageInterface;

class PostNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_NORMALIZE = 'AppSerializerPostAlREADY';

    public function __construct(
      private StorageInterface $storage
    ) {
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        $alreadyNormalize = $context[self::ALREADY_NORMALIZE] ?? false;

        return $data instanceof Post && $alreadyNormalize === false;
    }

    /**
     * @param Post $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $context[self::ALREADY_NORMALIZE] = true;
        $object->setImageUrl($this->storage->resolveUri($object, 'imageFile'));

        return $this->normalizer->normalize($object, $format, $context);
    }
}