<?php


namespace App\Controller;


use App\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostImageController
{
    public function __invoke(Request $request): Post
    {
        $post = $request->attributes->get('data');
        if (!$post instanceof Post) {
            throw new NotFoundHttpException('No Post');
        }

        $post->setImageFile($request->files->get('file'));
        $post->setUpdatedAt(new \DateTime());
        return $post;
    }
}