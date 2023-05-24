<?php

namespace controller;

require_once dirname(__DIR__) . '/../betterphp/utils/Controller.php';


use betterphp\utils\Controller;
use Exception;
use model\User;
use PDO;

#[\betterphp\utils\attributes\Controller]
class AuthController extends Controller
{

    public function login(string $email, string $plain_text_password): false|User {
        $stmt = self::$connection->prepare('SELECT * FROM HL_User WHERE u_email = :email');
        $stmt->bindParam('email', $email);
        $stmt->execute();

        $result = $stmt->fetch();
        if (!$result) {
            return false;
        }

        if(!password_verify($plain_text_password, $result['u_password'])) {
            return false;
        }

        $user = User::getFromRow($result);
        $_SESSION['user'] = serialize($user);
        return $user;
    }

    public function register(string $username, string $password, string $email): false|User
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = self::$connection->prepare('INSERT INTO HL_User (u_name, u_email, u_password) VALUES (:username, :email, :password)');
        $stmt->bindParam('username', $username);
        $stmt->bindParam('email', $email);
        $stmt->bindParam('password', $hashed_password);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return false;
        }

        return $this->login($email, $password);
    }
}