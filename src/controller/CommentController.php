<?php

namespace controller;

use betterphp\utils\ApiException;
use betterphp\utils\Controller;
use betterphp\utils\HttpErrorCodes;
use DateTime;
use Exception;
use model\Comment;

#[\betterphp\utils\attributes\Controller]
class CommentController extends Controller
{
    public function createComment($commentText, $postId, $userId): false|Comment {
        $stmt = self::$connection->prepare('INSERT INTO HL_Comment (c_text, c_u_id, c_p_id, c_date) VALUES (:commentText, :userId, :postId, :date)');
        $stmt->bindParam('commentText', $commentText);
        $stmt->bindParam('userId', $userId);
        $stmt->bindParam('postId', $postId);
        $date = new DateTime();
        $dateFormatted = $date->format('Y-m-d H:i:s');
        $stmt->bindParam('date', $dateFormatted);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            return false;
        }

        $id = self::$connection->lastInsertId();

        if(!$id) {
            return false;
        }

        return new Comment($id, $commentText,$date, $userId, $postId);
    }

    private function getCommentById(int $commentId): Comment {
        $stmt = self::$connection->prepare('SELECT * FROM HL_Comment WHERE c_id = :commentId');
        $stmt->bindParam('commentId', $commentId);
        $stmt->execute();

        $result = $stmt->fetch();

        if(!$result) {
            throw new ApiException(HttpErrorCodes::HTTP_NOT_FOUND, 'Comment not found');
        }

        return Comment::getFromRow($result);
    }

    public function deleteComment(int $commentId): bool {

        $comment = $this->getCommentById($commentId);

        $sessionUserId = unserialize($_SESSION['user'])->id;

        if($comment->userId != $sessionUserId) {
            throw new ApiException(HttpErrorCodes::HTTP_FORBIDDEN, 'You are not allowed to delete this comment');
        }

        $stmt = self::$connection->prepare('DELETE FROM HL_Comment WHERE c_id = :commentId');
        $stmt->bindParam('commentId', $commentId);

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

    public function getByPostId(int $postId): false|array
    {
        $stmt = self::$connection->prepare('SELECT * FROM HL_Comment WHERE c_p_id = :postId order by c_date desc');
        $stmt->bindParam('postId', $postId);
        $stmt->execute();

        $result = $stmt->fetchAll();

        if($result === false) {
            return false;
        }

        $comments = [];

        foreach($result as $row) {
            $comments[] = Comment::getFromRow($row);
        }

        return $comments;

    }
}