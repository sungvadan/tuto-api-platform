<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Dependency;

class DependencyDataPersister implements ContextAwareDataPersisterInterface
{

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Dependency;
    }

    public function persist($data, array $context = [])
    {

    }

    public function remove($data, array $context = [])
    {
    }
}