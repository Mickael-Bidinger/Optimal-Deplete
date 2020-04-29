<?php

namespace App\repository;


use App\entity\Affix;
use MB\Database;
use MB\FileHandler;

class AffixRepository
{
    private const IMAGE_WIDTH = 30;
    private const IMAGE_HEIGHT = 30;

    public function create(int $id, string $name, string $imageUrl): Affix
    {
        $imageLocal = \mb_strtolower("images/affix/$name.png", 'UTF-8');
        $imageLocal = \mb_ereg_replace(':', '', $imageLocal);
        $imageLocal = \mb_ereg_replace(' ', '_', $imageLocal);

        FileHandler::resizeImage($imageUrl, $imageLocal, self::IMAGE_WIDTH, self::IMAGE_HEIGHT);

        $db = new Database();
        $db->execute('
                INSERT INTO affix
                (id, name, image, starting_level)
                VALUES
                (:id, :name, :image, :starting_level)',
            [
                ':id' => $id,
                ':name' => $name,
                ':image' => $imageLocal,
                ':starting_level' => 0,
            ]
        );
        $db = null;

        return new Affix($id, $name, $imageLocal, 0);
    }

    public function find(int $id): ?Affix
    {
        $db = new Database();
        $affix = $db->queryOne('SELECT * FROM affix WHERE id = ?', [$id]);
        $db = null;

        if ($affix === false || empty($affix)) {
            return null;
        }

        return new Affix($affix['id'], $affix['name'], $affix['image'], $affix['starting_level']);
    }

    /**
     * @return Affix[]|null
     */
    public function list(): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('SELECT * FROM affix ORDER BY name');
        $db = null;

        if ($response === false) {
            return null;
        }

        $affixes = [];
        foreach ($response as $affix) {
            $affixes[(int)$affix['id']] = new Affix($affix['id'], $affix['name'], $affix['image'], $affix['starting_level']);
        }

        return $affixes;
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }

        foreach ($this->list() as $affix) {
            FileHandler::deleteFile($affix->getImage());
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE affix;');
        $db = null;

    }

    public function updateStartingLevel($id, $starting_level)
    {
        $db = new Database();
        $db->execute(
            'UPDATE affix SET starting_level = :starting_level WHERE id = :id',
            [':id' => $id, ':starting_level' => $starting_level]
        );
        $db = null;
    }

}