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
use controller\ImageController;
use Exception;

#[Service]
class ImageUploadService
{

    #[Route('/image')]
    #[POST]
    public function uploadImage(#[BodyParam] $body): Response {
        $fileToUpload = $_FILES['fileToUpload'];

        if(!isset($fileToUpload)) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, "Image is null");
        }

        $imageController = ImageController::getInstance();

        try {
            $image = $imageController->saveImage($fileToUpload);
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Image could not be uploaded");
        }


        return Response::created("Image uploaded successfully", $image);
    }

    #[Route('/image')]
    #[GET]
    public function getImage(#[QueryParam] $img): Response {
        if(!isset($img)) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, "Image is null");
        }

        $imageController = ImageController::getInstance();
        $image = $imageController->getImage($img);

        if(!$image) {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Image could not be found");
        }

        header('Content-Type: image/png');
        echo $image;
        exit();
    }
}