<?php

namespace App\entity;


class Realm
{
    private $id;
    private $regionId;

    public function __construct(int $id, int $regionId)
    {
        $this->id = $id;
        $this->regionId = $regionId;
    }

    public function __toString():string
    {
        return $this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRegionId(): int
    {
        return $this->regionId;
    }

}