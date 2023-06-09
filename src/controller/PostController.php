<?php

namespace controller;
require_once dirname(__DIR__) . '/../betterphp/utils/Controller.php';

use betterphp\utils\ApiException;
use betterphp\utils\Controller;
use betterphp\utils\HttpErrorCodes;
use DateTime;
use Exception;
use model\Like;
use model\Post;
use PDO;

#[\betterphp\utils\attributes\Controller]
class PostController extends Controller
{
    public function createPost(string $image, string $description, bool $important): false|Post {

        if($important) {
            $isTeacher = unserialize($_SESSION['user'])->userType == 'teacher';
            if($isTeacher === false) {
                throw new ApiException(HttpErrorCodes::HTTP_FORBIDDEN, "Only teachers can create important posts");
            }
        }

        $date = new DateTime();
        $formatedDate = $date->format('Y-m-d H:i:s');
        $user = unserialize($_SESSION['user']);
        $userId = $user->id;
        $stmt = self::$connection->prepare('INSERT INTO HL_Post (p_image, p_description, p_date, p_u_id, p_important) VALUES (:image, :description, :date, :userId, :important)');
        $stmt->bindParam('image', $image);
        $stmt->bindParam('description', $description);
        $stmt->bindParam('date', $formatedDate);
        $stmt->bindParam('userId', $userId);
        $stmt->bindParam('important', $important, PDO::PARAM_BOOL);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
        $id = self::$connection->lastInsertId();

        return new Post(
            $id,
            $image,
            $description,
            $date,
            $important,
            $userId
        );
    }

    public function deletePost($id): bool
    {
        if(!$this->isPostedByUser($id)) {
            throw new ApiException(HttpErrorCodes::HTTP_FORBIDDEN, 'You are not allowed to delete this post');
        }

        $stmt = self::$connection->prepare('DELETE FROM HL_Post WHERE p_id = :id');
        $stmt->bindParam('id', $id);


        try {
            $stmt->execute();
            $count = $stmt->rowCount();

            if ($count == 0) {
                return false;
            }

        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function getAllPostsPaginated($page, $pageSize): false|array
    {
        $offset = ($page - 1) * $pageSize;
        $stmt = self::$connection->prepare('SELECT * FROM HL_Post order by p_date desc LIMIT :pageSize OFFSET :offset');
        $stmt->bindParam('pageSize', $pageSize);
        $stmt->bindParam('offset', $offset);
        $stmt->execute();

        $result = $stmt->fetchAll();

        if ($result === false) {
            return false;
        }

        $posts = [];
        foreach ($result as $row) {
            $posts[] = Post::getFromRow($row);
        }

        return $posts;
    }

    public function getNormalPostsPaginated($page, $pageSize) {
        $offset = ($page - 1) * $pageSize;
        $stmt = self::$connection->prepare('SELECT * FROM HL_Post where p_important = false order by p_date desc LIMIT :pageSize OFFSET :offset');
        $stmt->bindParam('pageSize', $pageSize);
        $stmt->bindParam('offset', $offset);
        $stmt->execute();

        $result = $stmt->fetchAll();

        if ($result === false) {
            return false;
        }

        $posts = [];
        foreach ($result as $row) {
            $posts[] = Post::getFromRow($row);
        }

        return $posts;
    }

    public function getImportantPostsPaginated($page, $pageSize) {
        $offset = ($page - 1) * $pageSize;
        $stmt = self::$connection->prepare('SELECT * FROM HL_Post where p_important = true order by p_date desc LIMIT :pageSize OFFSET :offset');
        $stmt->bindParam('pageSize', $pageSize);
        $stmt->bindParam('offset', $offset);
        $stmt->execute();

        $result = $stmt->fetchAll();

        if ($result === false) {
            return false;
        }

        $posts = [];
        foreach ($result as $row) {
            $posts[] = Post::getFromRow($row);
        }

        return $posts;
    }

    public function getAllPosts(): false|array
    {
        $stmt = self::$connection->prepare('SELECT * FROM HL_Post order by p_date desc');
        $stmt->execute();


        $result = $stmt->fetchAll();

        if ($result === false) {

            return false;
        }

        $posts = [];
        foreach ($result as $row) {
            $posts[] = Post::getFromRow($row);
        }

        return $posts;
    }

    public function getPostById($id): false|Post
    {
        $stmt = self::$connection->prepare('SELECT * FROM HL_Post WHERE p_id = :id');
        $stmt->bindParam('id', $id);
        $stmt->execute();

        $result = $stmt->fetch();
        if ($result === false) {
            return false;
        }

        return Post::getFromRow($result);
    }

    /**
     * @param $postId
     * @return bool true if the post was liked, false if it was unliked
     * @throws ApiException if the user is not logged in
     */
    public function toggleLike($postId): bool
    {
        $sessionUserId = unserialize($_SESSION['user'])->id;

        $isLiked = LikeController::getInstance()->isLiked($postId, $sessionUserId);
        if ($isLiked) {

            LikeController::getInstance()->deleteLike($postId, $sessionUserId);
            return false;
        } else {
            LikeController::getInstance()->createLike($postId, $sessionUserId);
            return true;
        }
    }


    public function getLikesOfPost($postId): false|array
    {
        $stmt = self::$connection->prepare('SELECT * FROM HL_Like WHERE l_p_id = :postId');
        $stmt->bindParam('postId', $postId);
        $stmt->execute();

        $result = $stmt->fetchAll();
        if ($result === false) {
            return false;
        }

        $likes = [];
        foreach ($result as $row) {
            $likes[] = Like::getFromRow($row);
        }

        return $likes;
    }

    public function getLikeCount($postId): false | int
    {
        $stmt = self::$connection->prepare('SELECT COUNT(*) FROM HL_Like WHERE l_p_id = :postId');
        $stmt->bindParam('postId', $postId);
        $stmt->execute();

        $result = $stmt->fetch();
        if (!$result) {
            return false;
        }

        return $result[0];
    }

    public function isLikedByUser(int $id): bool
    {
        $sessionUserId = unserialize($_SESSION['user'])->id;

        $stmt = self::$connection->prepare('SELECT * FROM HL_Like WHERE l_p_id = :postId AND l_u_id = :userId');
        $stmt->bindParam('postId', $id);
        $stmt->bindParam('userId', $sessionUserId);
        $stmt->execute();

        $result = $stmt->fetch();
        if (!$result) {
            return false;
        }

        return true;
    }

    public function isPostedByUser(int $id): bool
    {
        $sessionUserId = unserialize($_SESSION['user'])->id;

        $stmt = self::$connection->prepare('SELECT * FROM HL_Post WHERE p_id = :postId AND p_u_id = :userId');
        $stmt->bindParam('postId', $id);
        $stmt->bindParam('userId', $sessionUserId);
        $stmt->execute();

        $result = $stmt->fetch();
        if (!$result) {
            return false;
        }

        return true;
    }

    public function getPostsByUser(int $userId): array|false {
        $stmt = self::$connection->prepare("SELECT * FROM hl_post where p_u_id = :userId order by p_date");
        $stmt->bindParam("userId", $userId);
        $stmt->execute();

        $result = $stmt->fetchAll();

        if($result === false) {
            return false;
        }

        $posts = [];

        foreach ($result as $row) {
            $posts[]=Post::getFromRow($row);
        }

        return $posts;
    }

    public function getPostsLikedByUser(int $userId): array|false
    {
        $stmt = self::$connection->prepare("select * from hl_post where p_id in (select l_p_id from hl_like where l_u_id = :userId)");
        $stmt->bindParam("userId", $userId);
        $stmt->execute();

        $result = $stmt->fetchAll();

        if($result === false) {
            return false;
        }

        $posts = [];
        foreach ($result as $row) {
            $post=Post::getFromRow($row);
            $post->user;
            $posts[]=$post;
        }

        return $posts;
    }


}