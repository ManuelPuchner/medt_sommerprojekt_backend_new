<?php

namespace service;

use betterphp\utils\attributes\BodyParam;
use betterphp\utils\attributes\DELETE;
use betterphp\utils\attributes\PathParam;
use betterphp\utils\attributes\POST;
use betterphp\utils\attributes\ProtectedRoute;
use betterphp\utils\attributes\Route;
use betterphp\utils\attributes\Service;
use betterphp\utils\HttpErrorCodes;
use betterphp\utils\Response;
use controller\CommentController;

#[Service]
class CommentService
{
    #[Route('/comment')]
    #[POST]
    #[ProtectedRoute]
    public function createComment(#[BodyParam] array $body): Response {
        $comment = $body['comment_text'];
        $postId = $body['postId'];
        $sessionUserId = unserialize($_SESSION['user'])->id;

        if($comment == null || $postId == null) {
            return Response::error(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Comment or postId is null");
        }

        $commentObj = CommentController::getInstance()->createComment($comment, $postId, $sessionUserId);

        // to include the user in the response
        $commentObj->user;

        return Response::created("Comment created successfully", $commentObj);

    }

    #[Route('/comment/{id}')]
    #[DELETE]
    #[ProtectedRoute]
    public function deleteComment(#[PathParam] string $commentId): Response {
        if($commentId == null) {
            return Response::error(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Comment id is null");
        }

        $commentObj = CommentController::getInstance()->deleteComment($commentId);

        return Response::ok("Comment deleted successfully", $commentObj);
    }

}