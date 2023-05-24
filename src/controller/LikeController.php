<?php

namespace controller;

require_once dirname(__DIR__) . '/../betterphp/utils/Controller.php';
use betterphp\utils\Controller;

#[\betterphp\utils\attributes\Controller]
class LikeController extends Controller
{
    public function createLike(string $post_id, string $user_id): bool
    {
        $stmt = self::$connection->prepare('INSERT INTO HL_Like (l_p_id, l_u_id) VALUES (:post_id, :user_id)');
        $stmt->bindParam('post_id', $post_id);
        $stmt->bindParam('user_id', $user_id);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return false;
        }
        return true;
    }

    public function deleteLike(string $post_id, string $user_id): bool
    {
        $stmt = self::$connection->prepare('DELETE FROM HL_Like WHERE l_p_id = :post_id AND l_u_id = :user_id');
        $stmt->bindParam('post_id', $post_id);
        $stmt->bindParam('user_id', $user_id);
        $stmt->execute();

        if($stmt->rowCount() == 0) {
            return false;
        }
        return true;
    }

    public function isLiked(string $post_id, string $user_id): bool
    {
        $stmt = self::$connection->prepare('SELECT * FROM HL_Like WHERE l_p_id = :post_id AND l_u_id = :user_id');
        $stmt->bindParam('post_id', $post_id);
        $stmt->bindParam('user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch();
        if($result) {
            return true;
        } else {
            return false;
        }
    }
}