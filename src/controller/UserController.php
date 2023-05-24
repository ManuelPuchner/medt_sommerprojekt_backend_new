<?php

namespace controller;

use betterphp\utils\ApiException;
use betterphp\utils\HttpErrorCodes;
use Exception;
use model\User;
use PDO;

require_once dirname(__DIR__) . '/../betterphp/utils/Controller.php';


use betterphp\utils\Controller;

#[\betterphp\utils\attributes\Controller]
class UserController extends Controller
{

    public function getUserById(int $userId): false|User
    {
        $stmt = self::$connection->prepare('SELECT * FROM HL_User WHERE u_id = :id');
        $stmt->bindParam('id', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        if($result) {
            return User::getFromRow($result);
        } else {
            return false;
        }
    }

    public function getById(string $id): ?User
    {
        $stmt = self::$connection->prepare('SELECT * FROM HL_User WHERE u_id = :id');
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $result = $stmt->fetch();
        if($result) {
            return User::getFromRow($result);
        } else {
            throw new ApiException(HttpErrorCodes::HTTP_NOT_FOUND, "User not found");
        }
    }

    public function getByName(string $name): ?User
    {
        $stmt = self::$connection->prepare('SELECT * FROM HL_User WHERE u_name = :name');
        $stmt->bindParam('name', $name);
        $stmt->execute();
        $result = $stmt->fetch();
        if($result) {
            return User::getFromRow($result);
        } else {
            throw new ApiException(HttpErrorCodes::HTTP_NOT_FOUND, "User not found");
        }
    }

    public function getBySession(): ?User
    {
        return unserialize($_SESSION['user']);
    }

    public function getLikedPosts(): array
    {
        $posts = [];

        return $posts;
    }
}