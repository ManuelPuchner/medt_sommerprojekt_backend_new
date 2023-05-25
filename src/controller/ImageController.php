<?php

namespace controller;

use betterphp\utils\ApiException;
use betterphp\utils\Controller;
use betterphp\utils\HttpErrorCodes;

#[\betterphp\utils\attributes\Controller]
class ImageController extends Controller
{
    private static string $IMAGE_UPLOAD_DIR = '/var/www/html/images/';

    /**
     * @throws ApiException
     */
    public function saveImage(array $fileToUpload): string {
        $target_file = self::$IMAGE_UPLOAD_DIR . basename($fileToUpload['name']);

        $check = getimagesize($fileToUpload['tmp_name']);

        if ($check === false) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, "File is not an image");
        }

        if(file_exists($target_file)) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, "File already exists");
        }

        if($fileToUpload['size'] > 500000) {
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, "File is too large");
        }

        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif"){
            throw new ApiException(HttpErrorCodes::HTTP_BAD_REQUEST, "Only JPG, JPEG, PNG & GIF files are allowed");
        }

        $img_name_generated = uniqid() . "-" . basename($fileToUpload["name"]);
        $target_file = self::$IMAGE_UPLOAD_DIR . $img_name_generated;

        @mkdir(self::$IMAGE_UPLOAD_DIR, 0777, true);

        if (move_uploaded_file($fileToUpload["tmp_name"], $target_file)) {
            return $img_name_generated;
        } else {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "Sorry, there was an error uploading your file.");
        }
    }

    public function getImage(string $imgName): string {
        $imagePath = self::$IMAGE_UPLOAD_DIR . $imgName;

        if (!file_exists($imagePath)) {
            throw new ApiException(HttpErrorCodes::HTTP_NOT_FOUND, "Image not found");
        }

        return file_get_contents($imagePath);
    }
}