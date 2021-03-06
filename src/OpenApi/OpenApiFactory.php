<?php

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Model\RequestBody;
use ApiPlatform\Core\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {
    }


    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        /** @var PathItem $path */
        foreach ($openApi->getPaths()->getPaths() as $key => $path) {
            if ($path->getGet() && $path->getGet()->getSummary() === 'hidden') {
                $openApi->getPaths()->addPath($key, $path->withGet(null));
            };
        }

        $securitySchemes = $openApi->getComponents()->getSecuritySchemes();
        $securitySchemes['bearerAuth'] = new \ArrayObject([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT'
        ]);

//        $openApi = $openApi->withSecurity(['cookieAuth' => []]);
        $schemas = $openApi->getComponents()->getSchemas();
        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'username' => [
                    'type' => 'string',
                    'example' => 'svd.phan@gmail.com'
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'test'
                ]
            ]
        ]);

        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                ],
            ]
        ]);

        $pathItem = new PathItem(
            post: new Operation(
                operationId: 'PostLogin',
                tags: ['Auth'],
                responses: [
                   '200' => [
                       'description' => 'Utilisateur connect??',
                       'content' => [
                           'application/json' => [
                               'schema' => [
                                   '$ref' => '#/components/schemas/Token'
                               ]
                           ]
                       ]
                   ]
                ],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials'
                            ]
                        ]
                    ])
                )
            )
        );

        $openApi->getPaths()->addPath('/api/login', $pathItem);

        $pathItem = new PathItem(
            post: new Operation(
                operationId: 'PostLogout',
                tags: ['Auth'],
                responses: [
                    '204' => []
                ],
            )
        );

        $openApi->getPaths()->addPath('/logout', $pathItem);


        $meOperation  = $openApi->getPaths()->getPath('/api/me')->getGet()->withParameters([]);
        $mePath  = $openApi->getPaths()->getPath('/api/me')->withGet($meOperation);
        $openApi->getPaths()->addPath('/api/me', $mePath);

        return $openApi;
    }
}