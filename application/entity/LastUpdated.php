<?php

namespace App\entity;


class LastUpdated
{
    private $id;
    private $dungeonId;
    private $realmId;
    private $regionId;
    private $lastDungeon;
    private $lastPeriod;

    public function __construct(
        int $id,
        int $dungeonId,
        int $realmId,
        int $regionId,
        int $lastDungeon,
        int $lastPeriod
    )
    {
        $this->id = $id;
        $this->dungeonId = $dungeonId;
        $this->realmId = $realmId;
        $this->regionId = $regionId;
        $this->lastDungeon = $lastDungeon;
        $this->lastPeriod = $lastPeriod;
    }

    public function __toString(): string
    {
        return "$this->dungeonId.$this->realmId.$this->regionId";
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLastPeriod(): int
    {
        return $this->lastPeriod;
    }

    public function getRegionId(): int
    {
        return $this->regionId;
    }

    public function getRealmId(): int
    {
        return $this->realmId;
    }

    public function getDungeonId(): int
    {
        return $this->dungeonId;
    }

    public function getLastDungeon(): int
    {
        return $this->lastDungeon;
    }


}