<?php

namespace App\entity;



class Affix
{
    private $id;
    private $image;
    private $name;
    private $startingLevel;

    public function __construct(int $id, string $name, string $image, int $startingLevel)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->startingLevel = $startingLevel;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartingLevel(): int
    {
        return $this->name;
    }


}