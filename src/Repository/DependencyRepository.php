<?php

namespace App\Repository;

use App\Entity\Dependency;

class DependencyRepository
{
    public function __construct(private string $rootPath)
    {
    }

    /**
     * @return Dependency[]
     */
    public function findAll(): array
    {
        $path = $this->rootPath . '/composer.json';
        $json = json_decode(file_get_contents($path), true);
        $items = [];
        foreach ($json['require'] as $name => $version) {
            $items[] = new Dependency($name, $version);
        }

        return $items;
    }

    public function find(string $uuid): ?Dependency
    {
        $dependencies = $this->findAll();
        foreach ($dependencies as $dependency) {
            if ($dependency->getUuid() === $uuid) {
                return $dependency;
            }
        }

        return null;
    }
}