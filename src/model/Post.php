<?php

namespace model;

use betterphp\Orm\Column;
use betterphp\Orm\Entity;
use betterphp\Orm;
use controller\CommentController;
use controller\PostController;
use controller\UserController;
use DateTime;
use Exception;

#[Entity('HL_Post')]
class Post implements \JsonSerializable
{

    private static string $IMAGE_PATH_PREFIX;

    #[Column([
        'name' => 'p_id',
        'type' => 'BIGINT',
    ])]
    #[Orm\PrimaryKey]
    #[Orm\AutoIncrement]
    private int $id;

    #[Column([
        'name' => 'p_image',
        'type' => 'VARCHAR(255)'
    ])]
    private string $image;

    #[Column([
        'name' => 'p_description',
        'type' => 'VARCHAR(255)'
    ])]
    private string $description;

    #[Column([
        'name' => 'p_date',
        'type' => 'timestamp'
    ])]
    private DateTime $date;

    #[Column([
        'name' => 'p_u_id',
        'type' => 'BIGINT'
    ])]
    private int $userId;

    #[Column([
        'name' => 'p_important',
        'type' => 'BOOLEAN'
    ])]
    private bool $important;


    private ?User $user;

    private ?array $comments;

    private ?int $likeCount;

    private ?array $likes;

    private ?bool $likedByUser;

    private ?bool $isPostedByUser;

    public function __construct(int $id, string $image, string $description, DateTime $date, bool $important, int $userId)
    {
        $this->id = $id;
        $this->image = $image;
        $this->description = $description;
        $this->date = $date;
        $this->userId = $userId;
        $this->important = $important;

        $env = parse_ini_file($_SERVER["DOCUMENT_ROOT"]. '/.env');

        self::$IMAGE_PATH_PREFIX = $env['SERVER_URL'] . '/api/image/?img=';
    }

    public function __get(string $name)
    {
        if ($name === 'id') {
            return $this->id;
        }
        if ($name === 'image') {
            return $this->image;
        }
        if ($name === 'description') {
            return $this->description;
        }
        if ($name === 'date') {
            return $this->date;
        }
        if ($name === 'userId') {
            return $this->userId;
        }
        if ($name === 'important') {
            return $this->important;
        }
        if($name === 'user'){
            if(!isset($this->user)){
                $this->user = UserController::getInstance()->getById($this->userId);
            }
            return $this->user;
        }
        if($name === 'comments'){
            if(!isset($this->comments)){
                $this->comments = CommentController::getInstance()->getByPostId($this->id);
            }
            return $this->comments;
        }
        if($name === 'likeCount'){
            if(!isset($this->likeCount)){
                $this->likeCount = PostController::getInstance()->getLikeCount($this->id);
            }
            return $this->likeCount;
        }
        if($name === 'likes'){
            if(!isset($this->likes)){
                $this->likes = PostController::getInstance()->getLikesOfPost($this->id);
            }
            return $this->likes;
        }
        if($name === 'likedByUser'){
            if(!isset($this->likedByUser)){
                $this->likedByUser = PostController::getInstance()->isLikedByUser($this->id);
            }
            return $this->likedByUser;
        }
        if($name === 'isPostedByUser'){
            if(!isset($this->isPostedByUser)){
                $this->isPostedByUser = PostController::getInstance()->isPostedByUser($this->id);
            }
            return $this->isPostedByUser;
        }
        throw new Exception("Property $name does not exist");
    }

    public function __set(string $name, mixed $value)
    {
        if ($name === 'image') {
            $this->image = $value;
            return;
        }
        if ($name === 'description') {
            $this->description = $value;
            return;
        }
        if ($name === 'date') {
            $this->date = $value;
            return;
        }
        throw new Exception("Property $name does not exist");
    }

    public function jsonSerialize(): array
    {
        $timezonedDate = clone $this->date;
        $timezonedDate->setTimezone(new \DateTimeZone('Europe/Berlin'));

        $postJson = [
            'id' => $this->id,
            'image' => self::$IMAGE_PATH_PREFIX . $this->image,
            'description' => $this->description,
            'date' => $timezonedDate->format('Y-m-d H:i:s'),
            'userId' => $this->userId,
            'important' => $this->important
        ];

        if(isset($this->user)){
            $postJson['user'] = $this->user;
        }

        if(isset($this->comments)){
            $postJson['comments'] = $this->comments;
        }

        if(isset($this->likeCount)){
            $postJson['likeCount'] = $this->likeCount;
        }

        if(isset($this->likes)){
            $postJson['likes'] = $this->likes;
        }

        if(isset($this->likedByUser)){
            $postJson['isLikedByUser'] = $this->likedByUser;
        }

        if (isset($this->isPostedByUser)) {
            $postJson['isPostedByUser'] = $this->isPostedByUser;
        }

        return $postJson;
    }

    public static function getFromRow(array $row): Post
    {
        return new Post(
            $row['p_id'],
            $row['p_image'],
            $row['p_description'],
            new DateTime($row['p_date']),
            $row['p_important'],
            $row['p_u_id']
        );
    }


}