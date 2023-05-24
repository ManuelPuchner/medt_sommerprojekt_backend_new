<?php

namespace service;

use betterphp\utils\ApiException;
use betterphp\utils\attributes\BodyParam;
use betterphp\utils\attributes\GET;
use betterphp\utils\attributes\POST;
use betterphp\utils\attributes\QueryParam;
use betterphp\utils\attributes\Route;
use betterphp\utils\attributes\Service;
use betterphp\utils\HttpErrorCodes;
use betterphp\utils\Response;
use controller\AuthController;
use controller\UserController;

#[Service]
class UserService
{
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
        if (!$user) {
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
    public function getLikedPosts(): Response{
        $userController = UserController::getInstance();
        $posts = $userController->getLikedPosts();

        return Response::ok('Posts fetched successfully', $posts);
    }

}