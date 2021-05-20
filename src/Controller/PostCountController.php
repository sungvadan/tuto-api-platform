<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;

class PostCountController
{
    public function __construct(
        private PostRepository $postRepository
    ) {
    }

    public function __invoke(Request $request): int
    {
        $onlineRequest = $request->get('online');
        $condition = [];
        if ($onlineRequest) {
            $condition['online'] = $onlineRequest === '1' ? true : false;
        }

        return $this->postRepository->count($condition);
    }
}