<?php

namespace App\entity;


class Dungeon
{
    private $id;
    private $chest1;
    private $chest2;
    private $chest3;
    private $image;
    private $name;

    public function __construct(int $id, string $name, string $image, int $chest1, int $chest2, int $chest3)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->chest1 = $chest1;
        $this->chest2 = $chest2;
        $this->chest3 = $chest3;
    }

    public function __toString():string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getChest1(): int
    {
        return $this->chest1;
    }

    public function getChest2(): int
    {
        return $this->chest2;
    }

    public function getChest3(): int
    {
        return $this->chest3;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getName(): string
    {
        return $this->name;
    }


}