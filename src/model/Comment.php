<?php

namespace model;

use betterphp\Orm\Column;
use betterphp\Orm\Entity;
use betterphp\Orm;
use controller\PostController;
use controller\UserController;
use DateTime;
use Exception;
use JsonSerializable;

#[Entity('HL_Comment')]
class Comment implements JsonSerializable
{
    #[Column([
        'name' => 'c_id',
        'type' => 'BIGINT',
    ])]
    #[Orm\PrimaryKey]
    #[Orm\AutoIncrement]
    private int $id;

    #[Column([
        'name' => 'c_text',
        'type' => 'VARCHAR(255)',
    ])]
    private string $text;

    #[Column([
        'name' => 'c_date',
        'type' => 'timestamp',
    ])]
    private DateTime $date;

    #[Column([
        'name' => 'c_u_id',
        'type' => 'BIGINT',
    ])]
    private int $userId;

    #[Column([
        'name' => 'c_p_id',
        'type' => 'BIGINT',
    ])]
    private int $postId;

    private ?User $user;

    private ?Post $post;

    public function __construct(int $id, string $text, DateTime $date, int $userId, int $postId)
    {
        $this->id = $id;
        $this->text = $text;
        $this->date = $date;
        $this->userId = $userId;
        $this->postId = $postId;
    }

    public function jsonSerialize(): array
    {
        $timezonedDate = clone $this->date;
        $timezonedDate->setTimezone(new \DateTimeZone('Europe/Berlin'));

        $commentJson =  [
            'id' => $this->id,
            'text' => $this->text,
            'date' => $timezonedDate->format('Y-m-d H:i:s'),
            'userId' => $this->userId,
            'postId' => $this->postId,
        ];

        if(isset($this->user))
        {
            $commentJson['user'] = $this->user;
        }

        if(isset($this->post))
        {
            $commentJson['post'] = $this->post;
        }

        return $commentJson;
    }

    public function __get(string $name)
    {
        if($name === 'id')
        {
            return $this->id;
        }
        if ($name === 'text')
        {
            return $this->text;
        }

        if ($name === 'date')
        {
            return $this->date;
        }

        if ($name === 'userId')
        {
            return $this->userId;
        }

        if ($name === 'postId')
        {
            return $this->postId;
        }

        if ($name === 'user')
        {
            if (!isset($this->user))
            {
                $this->user = UserController::getInstance()->getUserById($this->userId);
            }

            return $this->user;
        }

        if ($name === 'post')
        {
            if (!isset($this->post))
            {
                $this->post = PostController::getInstance()->getPostById($this->postId);
            }

            return $this->post;
        }

        throw new Exception("Unknown property $name");
    }

    public static function getFromRow(array $row): Comment {
        return new Comment(
            $row['c_id'],
            $row['c_text'],
            new DateTime($row['c_date']),
            $row['c_u_id'],
            $row['c_p_id'],
        );
    }

}