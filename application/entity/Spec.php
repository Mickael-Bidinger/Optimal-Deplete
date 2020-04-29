<?php

namespace App\entity;


class Spec
{
    private $id;
    private $classId;
    private $roleId;
    private $name;
    private $image;

    public function __construct(
        int $id,
        int $classId,
        int $roleId,
        string $name,
        string $image
    )
    {
        $this->id = $id;
        $this->classId = $classId;
        $this->roleId = $roleId;
        $this->name = $name;
        $this->image = $image;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClassId(): int
    {
        return $this->classId;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImage(): string
    {
        return $this->image;
    }


}