<?php

namespace App\repository;


use App\entity\Dungeon;
use MB\Database;
use MB\FileHandler;

class DungeonRepository
{
    private const IMAGE_WIDTH = 170;
    private const IMAGE_HEIGHT = 85;

    public function create(int $id, string $name, string $imageUrl, int $chest1, int $chest2, int $chest3): Dungeon
    {
        $imageLocal = \mb_strtolower("images/dungeon/$name.png", 'UTF-8');
        $imageLocal = \mb_ereg_replace(':', '', $imageLocal);
        $imageLocal = \mb_ereg_replace(' ', '_', $imageLocal);

        FileHandler::resizeImage($imageUrl, $imageLocal, self::IMAGE_WIDTH, self::IMAGE_HEIGHT);

        $db = new Database();
        $db->execute('
            INSERT INTO dungeon
            (id, name, image, chest1, chest2, chest3)
            VALUES
            (:id, :name, :image, :chest1, :chest2, :chest3)
        ', [
            ':id' => $id,
            ':name' => $name,
            ':image' => $imageLocal,
            ':chest1' => $chest1,
            ':chest2' => $chest2,
            ':chest3' => $chest3,
        ]);
        $db = null;

        return new Dungeon($id, $name, $imageLocal, $chest1, $chest2, $chest3);
    }

    public function find(int $id): ?Dungeon
    {
        $db = new Database();
        $dungeon = $db->queryOne('SELECT * FROM dungeon WHERE id = ?', [$id]);
        $db = null;

        if ($dungeon === false) {
            return null;
        }

        return new Dungeon(
            $dungeon['id'],
            $dungeon['name'],
            $dungeon['image'],
            $dungeon['chest1'],
            $dungeon['chest2'],
            $dungeon['chest3']
        );
    }

    public function findOneByName(string $name): ?Dungeon
    {
        $db = new Database();
        $dungeon = $db->queryOne('SELECT * FROM dungeon WHERE name = ?', [$name]);
        $db = null;

        if ($dungeon === false || empty($dungeon)) {
            return null;
        }

        return new Dungeon(
            $dungeon['id'],
            $dungeon['name'],
            $dungeon['image'],
            $dungeon['chest1'],
            $dungeon['chest2'],
            $dungeon['chest3']
        );
    }

    /**
     * @return Dungeon[]|null
     */
    public function list(): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('SELECT * FROM dungeon ORDER BY name');
        $db = null;

        if ($response === false) {
            return null;
        }

        $dungeons = [];
        foreach ($response as $dungeon) {
            $dungeons[(int)$dungeon['id']] = new Dungeon(
                $dungeon['id'],
                $dungeon['name'],
                $dungeon['image'],
                $dungeon['chest1'],
                $dungeon['chest2'],
                $dungeon['chest3']
            );
        }

        return $dungeons;
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        foreach ($this->list() as $dungeon) {
            FileHandler::deleteFile($dungeon->getImage());
        }

        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE dungeon;');
        $db = null;

    }

}