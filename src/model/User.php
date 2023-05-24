<?php

namespace model;

use betterphp\Orm\Column;
use betterphp\Orm\Entity;
use betterphp\Orm;
use betterphp\utils\ApiException;
use betterphp\utils\HttpErrorCodes;
use Exception;
use JsonSerializable;

#[Entity('HL_User')]
class User implements JsonSerializable
{
    #[Column([
        'name' => 'u_id',
        'type' => 'BIGINT',
    ])]
    #[Orm\PrimaryKey]
    #[Orm\AutoIncrement]
    private int $id;

    #[Column([
        'name' => 'u_name',
        'type' => 'VARCHAR(255)'
    ])]
    private string $name;

    #[Column([
        'name' => 'u_email',
        'type' => 'VARCHAR(255)'
    ])]
    #[Orm\PrimaryKey]
    private string $email;

    #[Column([
        'name' => 'u_password',
        'type' => 'VARCHAR(255)'
    ])]
    private string $password;

    #[Column([
        'name' => 'u_userType',
        'type' => 'VARCHAR(25)'
    ])]
    private string $userType;


    public function __get(string $name)
    {
        if ($name === 'id') {
            return $this->id;
        }
        if ($name === 'name') {
            return $this->name;
        }
        if ($name === 'email') {
            return $this->email;
        }
        if ($name === 'password') {
            return $this->password;
        }
        if ($name === 'userType') {
            return $this->userType;
        }
        throw new Exception('Property ' . $name . ' not found');
    }

    public function __set(string $name, mixed $value)
    {
        if ($name === 'name') {
            $this->name = $value;
            return;
        }
        if ($name === 'email') {
            $this->email = $value;
            return;
        }
        if ($name === 'password') {
            $this->password = $value;
            return;
        }
        if ($name === 'userType') {
            $this->userType = $value;
            return;
        }
        throw new Exception('Property ' . $name . ' not found');
    }

    public function __construct(int $id = -1, string $name = '', string $email = '', string $password = '', string $userType = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->userType = $userType;
    }



    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'userType' => $this->userType,
        ];
    }

    public static function getMock(): User
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'test';
        $user->email = 'm.mock@students.htl-leonding.ac.at';
        $user->password = 'aöslkdhfapsuidbpad';
        $user->userType = 'student';
        return $user;
    }

    public static function getFromRow(array $row): User
    {
        try {
            $user = new User();
            if (!isset($row['u_id'])) throw new Exception('u_id not set in row' . print_r($row, true));
            $user->id = $row['u_id'];
            if (!isset($row['u_name'])) throw new Exception('u_name not set in row' . print_r($row, true));
            $user->name = $row['u_name'];
            if (!isset($row['u_email'])) throw new Exception('u_email not set in row'  . print_r($row, true));
            $user->email = $row['u_email'];
            if (!isset($row['u_password'])) throw new Exception('u_password not set in row' . print_r($row, true));
            $user->password = $row['u_password'];
            if (!isset($row['u_usertype'])) throw new Exception('u_userType not set in row' . print_r($row, true));
            $user->userType = $row['u_usertype'];
        } catch (Exception $e) {
            throw new ApiException(HttpErrorCodes::HTTP_INTERNAL_SERVER_ERROR, "User couldn't be parsed: " . $e->getMessage());
        }
        return $user;
    }
}