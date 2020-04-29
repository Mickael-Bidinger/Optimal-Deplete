<?php

namespace App\entity;


class Period
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

}