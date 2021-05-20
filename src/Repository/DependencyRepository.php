<?php

namespace App\Repository;

use App\Entity\Dependency;

class DependencyRepository
{
    /** @var string */
    private $path;

    public function __construct(private string $rootPath)
    {
        $this->path = $this->rootPath . '/composer.json';
    }

    /**
     * @return Dependency[]
     */
    public function findAll(): array
    {
        $json = json_decode(file_get_contents($this->path), true);
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

    public function persist(Dependency $dependency): void
    {
        $json = json_decode(file_get_contents($this->path), true);
        $json['require'][$dependency->getName()] = $dependency->getVersion();
        file_put_contents($this->path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function remove(Dependency $dependency): void
    {
        $json = json_decode(file_get_contents($this->path), true);
        unset($json['require'][$dependency->getName()]);
        file_put_contents($this->path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}