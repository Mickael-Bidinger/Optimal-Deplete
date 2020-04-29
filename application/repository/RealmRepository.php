<?php

namespace App\repository;


use App\entity\Realm;
use MB\Database;

class RealmRepository
{

    public function create(int $id, int $regionId): Realm
    {
        $db = new Database();
        $db->execute('
            INSERT INTO realm
            (id, region_id)
            VALUES
            (:id, :region_id)
        ', [
            ':id' => $id,
            ':region_id' => $regionId,
        ]);
        $db = null;

        return new Realm($id, $regionId);
    }

    /**
     * @return Realm[]|null
     */
    public function list(): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('
                SELECT
                    id,
                    region_id
                FROM realm
                ORDER BY region_id
           ');
        $db = null;

        if ($response === false) {
            return null;
        }

        $realms = [];
        foreach ($response as $realm) {
            $realms[(int)$realm['id']] = new Realm($realm['id'], $realm['region_id']);
        }

        return $realms;
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE realm;');
        $db = null;

    }

}