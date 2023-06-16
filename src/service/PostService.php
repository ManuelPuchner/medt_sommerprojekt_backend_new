<?php

namespace service;

use betterphp\utils\ApiException;
use betterphp\utils\attributes\BodyParam;
use betterphp\utils\attributes\DELETE;
use betterphp\utils\attributes\GET;
use betterphp\utils\attributes\PathParam;
use betterphp\utils\attributes\POST;
use betterphp\utils\attributes\ProtectedRoute;
use betterphp\utils\attributes\PUT;
use betterphp\utils\attributes\QueryParam;
use betterphp\utils\attributes\Route;
use betterphp\utils\attributes\Service;
use betterphp\utils\HttpErrorCodes;
use betterphp\utils\Response;
use controller\PostController;

#[Service]
class PostService
{
    #[Route('/post')]
    #[POST]
    #[ProtectedRoute]
    public function createPost(#[BodyParam] $body): Response {
        $image = $body['image'];
        $description = $body['description'];
        $important = $body['important'];

        if($image == null || $description == null) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, "Image or description is null");
        }

        if(!isset($important)) {
            $important = false;
        }

        $post = PostController::getInstance()->createPost($image, $description, $important);

        if ($post === false) {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Post could not be created");
        }

        return Response::created("Post created successfully", $post);
    }

    #[GET]
    #[Route('/post/important')]
    #[ProtectedRoute]
    public function getImportantPosts(#[QueryParam] $page, #[QueryParam] $length): Response {
        $posts = PostController::getInstance()->getImportantPostsPaginated($page, $length);

        if ($posts === false) {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Posts could not be fetched");
        }

        foreach ($posts as $post) {
            $comments = $post->comments;
            foreach ($comments as $comment)
                $comment->user;
            $post->user;
            $post->likeCount;
            $post->likedByUser;
            $post->isPostedByUser;
        }

        return Response::ok("Posts fetched successfully", $posts);
    }

    #[GET]
    #[Route('/post')]
    #[ProtectedRoute]
    public function getAllPosts(#[QueryParam] $page, #[QueryParam] $length): Response {

        if(!isset($page) || !isset($length)) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, "Page or length is null");
        }

        $posts = PostController::getInstance()->getNormalPostsPaginated($page, $length);

        foreach ($posts as $post) {
            $comments = $post->comments;
            foreach ($comments as $comment)
                $comment->user;
            $post->user;
            $post->likeCount;
            $post->likedByUser;
            $post->isPostedByUser;
        }

        if ($posts === false) {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Posts could not be fetched");
        }

        return Response::ok("Posts fetched successfully", $posts);
    }

    #[GET]
    #[Route('/post/{id}')]
    #[ProtectedRoute]
    public function getPostById(#[PathParam] $id): Response {
        $post = PostController::getInstance()->getPostById($id);

        if ($post === false) {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Post could not be fetched");
        }

        $comments = $post->comments;
        foreach ($comments as $comment)
            $comment->user;
        $post->user;

        return Response::ok("Post fetched successfully", $post);
    }


    #[Route('/post/{id}')]
    #[DELETE]
    #[ProtectedRoute]
    public function deletePost(#[PathParam] $id): Response {
        $deleted = PostController::getInstance()->deletePost($id);

        if (!$deleted) {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Post could not be deleted");
        }

        return Response::ok("Post deleted successfully");

    }

    #[Route('/post/like/{id}')]
    #[PUT]
    #[ProtectedRoute]
    public function likePost(#[PathParam] $postId): Response {

        try {
            $liked = PostController::getInstance()->toggleLike($postId);
        } catch (ApiException $e) {
            return new Response($e->getCode(), $e->getMessage());
        }

        if($liked) {
            return Response::ok("Post liked successfully");
        }

        return Response::ok("Post unliked successfully");
    }

    #[Route('/post/{id}/likes')]
    #[GET]
    #[ProtectedRoute]
    public function getLikedPosts(#[PathParam] $postId): Response {


        $posts = PostController::getInstance()->getLikesOfPost($postId);


        if ($posts === false) {
            return Response::error(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Posts could not be fetched");
        }

        return Response::ok("Posts fetched successfully", $posts);
    }

    #[Route('/post/{id}/likes/count')]
    #[GET]
    #[ProtectedRoute]
    public function getLikeCount(#[PathParam] $postId): Response {
        $count = PostController::getInstance()->getLikeCount($postId);

        if ($count === false) {
            return Response::error(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Posts could not be fetched");
        }

        return Response::ok("Posts fetched successfully", $count);
    }
}