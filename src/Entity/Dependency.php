<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ApiResource(
    itemOperations: [
        'get',
        'put' => [
            'denormalization_context' => [
                'groups' => ['put:Dependency']
            ]
        ],
        'delete'
    ],
    collectionOperations: [
        'get',
        'post',
    ],
    paginationEnabled: false
)]
class Dependency
{
    #[ApiProperty(
        identifier: true,
    )]
    private string $uuid;

    #[
        ApiProperty(
            description: 'Nom de la dépendance'
        ),
        Length(min: 2),
        NotBlank
    ]
    private string $name;

    #[
        ApiProperty(
            description: 'Version de la dépendance',
            openapiContext: [
                'example' => '5.2.*'
            ]

        ),
        Length(min: 2),
        NotBlank,
        Groups(['put:Dependency'])
    ]
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

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }
}