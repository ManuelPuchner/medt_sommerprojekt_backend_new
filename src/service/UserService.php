<?php

namespace service;

use betterphp\utils\ApiException;
use betterphp\utils\attributes\BodyParam;
use betterphp\utils\attributes\GET;
use betterphp\utils\attributes\POST;
use betterphp\utils\attributes\ProtectedRoute;
use betterphp\utils\attributes\QueryParam;
use betterphp\utils\attributes\Route;
use betterphp\utils\attributes\Service;
use betterphp\utils\HttpErrorCodes;
use betterphp\utils\Response;
use controller\AuthController;
use controller\PostController;
use controller\UserController;

#[Service]
class UserService
{

    #[Route('/echo')]
    #[GET]
    public function echo(#[QueryParam] string $message): Response{
        return Response::ok('Echo successful', $message);
    }

    #[Route('/user/getBy')]
    #[GET]
    public function getBy(#[QueryParam] string $by, #[QueryParam] string $value): Response{
        $studentController = UserController::getInstance();
        if ($by == 'id') {
            $user = $studentController->getById($value);
        } else if ($by == 'name') {
            $user = $studentController->getByName($value);
        } else if ($by == 'session') {
            $user = $studentController->getBySession();
        } else {
            throw new ApiException(HttpErrorCodes::HTTP_NOT_IMPLEMENTED, 'Unsupported search parameter');
        }
        $user->posts;

        return Response::ok('User fetched successfully', $user);
    }

    #[Route('/auth/register')]
    #[POST]
    public function register(#[BodyParam] array $data): Response{
        $authController = AuthController::getInstance();

        $username = $data['username'];
        $password = $data['password'];
        $email = $data['email'];

        if (!isset($username) || !isset($password) || !isset($email)) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, 'Missing required fields');
        }
        $user = $authController->register($username, $password, $email);
        if ($user === false) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, 'User already exists');
        }

        return Response::ok('User fetched successfully', $user);

    }

    #[Route('/auth/login')]
    #[POST]
    public function login(#[BodyParam] array $data): Response{
        $authController = AuthController::getInstance();

        $email = $data['email'];
        $password = $data['password'];

        if (!isset($email) || !isset($password)) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, 'Missing required fields');
        }
        $user = $authController->login($email, $password);
        if (!$user) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, 'Invalid mail or password');
        }

        return Response::ok('User fetched successfully', $user);

    }

    #[Route('/user/liked-posts')]
    #[GET]
    #[ProtectedRoute]
    public function getLikedPosts(): Response{
        $postController = PostController::getInstance();

        $userId = unserialize($_SESSION["user"])->id;

        $posts = $postController->getPostsLikedByUser($userId);

        if($posts === false) {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Posts could not be fetched");
        }

        return Response::ok('Posts fetched successfully', $posts);
    }

}