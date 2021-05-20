<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    itemOperations: ['get'],
    collectionOperations: ['get', 'post'],
    paginationEnabled: false
)]
class Dependency
{
    #[ApiProperty(
        identifier: true,
    )]
    private string $uuid;

    #[ApiProperty(
        description: 'Nom de la dépendance'
    )]
    private string $name;

    #[ApiProperty(
        description: 'Version de la dépendance',
        openapiContext: [
            'example' => '5.2.*'
        ]

    )]
    private string $version;

    public function __construct(
        string $name,
        string $version
    ) {
        $this->uuid = Uuid::v5( new Uuid('6ba7b811-9dad-11d1-80b4-00c04fd430c8'), $name);
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}