<?php

namespace App\repository;


use App\entity\LastUpdated;
use MB\Database;

class LastUpdatedRepository
{

    public function create(int $dungeonId, int $realmId, int $regionId): LastUpdated
    {
        $db = new Database();
        $id = $db->execute('
            INSERT INTO last_updated
            (dungeon_id, realm_id, region_id, last_period, last_dungeon, last_update_date)
            VALUES
            (:dungeon_id, :realm_id, :region_id, :last_period, :last_dungeon, :last_update_date)
        ', [
            ':dungeon_id' => $dungeonId,
            ':realm_id' => $realmId,
            ':region_id' => $regionId,
            ':last_period' => 0,
            ':last_dungeon' => 0,
            ':last_update_date' => '2020-01-01 00:00:00',
        ]);
        $db = null;

        return new LastUpdated(
            $id,
            $dungeonId,
            $realmId,
            $regionId,
            0,
            0
        );
    }

    /**
     * @param int $limit
     * @return LastUpdated[]|null
     */
    public function list(int $limit): ?array
    {
        // $limit étant un int, pas d'injection SQL possible.
        // par ailleurs, c'est moi qui set $limit dans le code (à des fins de tests)
        $limit = $limit === 0 ? '' : "LIMIT $limit";
        $db = new Database();
        $response = $db->queryMultiple("SELECT * FROM last_updated ORDER BY last_update_date, id $limit");
        $db = null;

        if ($response === false) {
            return null;
        }

        $LastsUpdated = [];
        foreach ($response as $LastUpdated) {
            $LastsUpdated[$LastUpdated['id']] = new LastUpdated(
                $LastUpdated['id'],
                $LastUpdated['dungeon_id'],
                $LastUpdated['realm_id'],
                $LastUpdated['region_id'],
                $LastUpdated['last_dungeon'],
                $LastUpdated['last_period']
            );
        }

        return $LastsUpdated;
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE last_updated;');
        $db = null;

    }

    public function update(int $id, int $lastPeriod, int $lastDungeon)
    {
        $db = new Database();
        $db->execute('
            UPDATE last_updated 
                SET last_period = :last_period,
                    last_dungeon = :last_dungeon,
                    last_update_date = :last_update_date
                WHERE id = :id
        ', [
            ':id' => $id,
            ':last_period' => $lastPeriod,
            ':last_dungeon' => $lastDungeon,
            ':last_update_date' => date_create()->format('Y-m-d H:i:s'),
        ]);
        $db = null;

    }

}