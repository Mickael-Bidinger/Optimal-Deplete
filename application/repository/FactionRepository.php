<?php

namespace App\repository;


use App\entity\Faction;
use MB\Database;

class FactionRepository
{
    public function create(string $name): Faction
    {
        $image = \mb_strtolower("images/faction/$name.png", 'UTF-8');

        $db = new Database();
        $id = $db->execute('
            INSERT INTO faction
            (name, image)
            VALUES
            (:name, :image)
        ', [
            ':name' => $name,
            ':image' => $image,
        ]);
        $db = null;

        return new Faction($id, $name, $image);
    }

    public function find(int $id): ?Faction
    {
        $db = new Database();
        $faction = $db->queryOne('SELECT * FROM faction WHERE id = ?', [$id]);
        $db = null;

        if ($faction === false) {
            return null;
        }

        return new Faction($faction['id'], $faction['name'], $faction['image']);
    }

    /**
     * @return Faction[]|null
     */
    public function list(): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('SELECT * FROM faction');
        $db = null;

        if ($response === false) {
            return null;
        }

        $factions = [];
        foreach ($response as $faction) {
            $factions[(int)$faction['id']] = new Faction($faction['id'], $faction['name'], $faction['image']);
        }

        return $factions;
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE faction;');
        $db = null;

    }

}