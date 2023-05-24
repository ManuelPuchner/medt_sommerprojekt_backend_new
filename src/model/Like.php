<?php

namespace model;

use betterphp\Orm\Column;
use betterphp\Orm\Entity;
use betterphp\Orm;
use JsonSerializable;

#[Entity('HL_Like')]
class Like implements JsonSerializable
{
    #[Column([
        'name' => 'l_id',
        'type' => 'BIGINT',
    ])]
    #[Orm\PrimaryKey]
    #[Orm\AutoIncrement]
    private int $id;

    #[Column([
        'name' => 'l_postId',
        'type' => 'BIGINT',
    ])]
    private int $postId;

    #[Column([
        'name' => 'l_userId',
        'type' => 'BIGINT',
    ])]
    private int $userId;

    public function __construct(int $id, int $postId, int $userId)
    {
        $this->id = $id;
        $this->postId = $postId;
        $this->userId = $userId;
    }

    public static function getFromRow(array $row): Like
    {
        return new Like(
            $row['l_id'],
            $row['l_p_id'],
            $row['l_u_id'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'postId' => $this->postId,
            'userId' => $this->userId,
        ];
    }
}