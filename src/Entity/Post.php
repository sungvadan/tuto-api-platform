<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Attribute\ApiGroupAuth;
use App\Controller\PostCountController;
use App\Controller\PostImageController;
use App\Controller\PostPublishController;
use App\Repository\PostRepository;
use App\Resolver\PostMaxIdResolver;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Valid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @Vich\Uploadable()
 */
#[
    ApiResource(
        collectionOperations: [
            'get' => [
                'openapi_context' => [
                    'security' => ['bearerAuth' => []]
                ],
//                'security' => 'is_granted("ROLE_USER")'
            ],
            'post',
            'count' => [
                'method' => 'get',
                'path' => '/posts/count',
                'controller' => PostCountController::class,
                'filters' => [],
                'pagination_enabled' => false,
                'openapi_context' => [
                    'summary' => 'récupérer le nombre d\'article',
                    'parameters' => [
                        [
                            'in' => 'query',
                            'name' => 'online',
                            'schema' => [
                                'type' => 'integer',
                                'maximum' => 1,
                                'minimum' => 0
                            ],
                            'description' => 'Filtre les article en ligne'
                        ]
                    ]
                ]
            ]
        ],
        itemOperations: [
            'put',
            'delete',
            'get' => [
                'normalization_context' => ['groups' => ['read:collection', 'read:item', 'read:Post']]
            ],
            'publish' => [
                'method' => 'POST',
                'path' => '/posts/{id}/publish',
                'controller' => PostPublishController::class,
                'status' => 200,
                'openapi_context' => [
                    'summary' => 'Permet de publier un article',
                    'requestBody' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    "type" => "object"
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'image' => [
                'method' => 'POST',
                'path' => '/posts/{id}/image',
//                'deserialize' => false,
                'controller' => PostImageController::class,
                'openapi_context' => [
                    'requestBody' => [
                        'content' => [
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'imageFile' => [
                                            'type' => 'string',
                                            'format' => 'binary'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        denormalizationContext: ['groups' => ['write:Post']],
//        paginationItemsPerPage: 2,
//        paginationMaximumItemsPerPage: 2,
        normalizationContext: ['groups' => 'read:collection'],
        paginationClientItemsPerPage: true,
        graphql: [
            'item_query',
            'collection_query' => [
                'pagination_type' => 'page'
            ],
            'create' => [
                'validation_groups' => ['create:Post'],
                'security' => 'is_granted("ROLE_USER")'
            ],
            'update',
            'delete',
            'maxIdQuery' => [
                'read' => false,
                'pagination_enabled' => false,
                'collection_query' => PostMaxIdResolver::class,
                'args' => [
                    'id' => ['type' => 'Int']
                ]
            ]
        ]
    ),
   ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'title' => 'partial'] ),
   ApiFilter(OrderFilter::class, properties: ['id', 'title'] ),
   ApiGroupAuth([
       'CAN_EDIT' => ['read:collection:Owner'],
       'ROLE_USER' => ['read:collection:User']
   ]),
]
class Post implements UserOwnedInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:collection'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:collection', 'put:Post', 'write:Post']),
        Length(min: 5, groups: ['create:Post'])
    ]
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:collection:User', 'put:Post', 'write:Post'])]
    private $slug;

    /**
     * @ORM\Column(type="text")
     */
    #[Groups(['read:collection', 'put:Post', 'write:Post'])]
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="posts", cascade="persist")
     */
    #[
        Groups(['read:item', 'put:Post', 'write:Post']),
        Valid()
    ]
    private $category;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean", options={"default"="0"})
     */
    #[
        Groups(['read:collection:Owner
        ']),
        ApiProperty(openapiContext: [
            'type' => 'boolean',
            'description' => 'en ligne ou pas'
        ])
    ]
    private $online = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string|null
     */
    #[Groups(['read:collection'])]
    private $image;

    /**
     * @Vich\UploadableField(mapping="post_images", fileNameProperty="image")
     * @var File|null
     */
    #[Groups(['write:Post'])]
    private $imageFile;

    /**
     * @var string|null
     */
    #[Groups(['read:collection'])]
    private $imageUrl;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     * @return Post
     */
    public function setImage(?string $image): Post
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param File|null $imageFile
     * @return Post
     */
    public function setImageFile(?File $imageFile): Post
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @param string|null $imageUrl
     * @return Post
     */
    public function setImageUrl(?string $imageUrl): Post
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }
}
