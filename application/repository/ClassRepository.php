<?php

namespace App\repository;


use App\entity\ClassEntity;
use MB\Database;
use MB\FileHandler;

class ClassRepository
{
    private const IMAGE_WIDTH = 35;
    private const IMAGE_HEIGHT = 35;

    public function create(int $id, string $name, string $imageUrl): ClassEntity
    {
        $imageLocal = \mb_strtolower("images/class/$name.png", 'UTF-8');
        $imageLocal = \mb_ereg_replace(':', '', $imageLocal);
        $imageLocal = \mb_ereg_replace(' ', '_', $imageLocal);

        FileHandler::resizeImage($imageUrl, $imageLocal, self::IMAGE_WIDTH, self::IMAGE_HEIGHT);

        $db = new Database();
        $db->execute('
            INSERT INTO class
            (id, name, image)
            VALUES
            (:id, :name, :image)
        ', [
            ':id' => $id,
            ':name' => $name,
            ':image' => $imageLocal,
        ]);
        $db = null;

        return new ClassEntity($id, $name, $imageLocal);
    }

    public function find(int $id): ?ClassEntity
    {
        $db = new Database();
        $class = $db->queryOne('SELECT * FROM class WHERE id = ?', [$id]);
        $db = null;

        if ($class === false || empty($class)) {
            return null;
        }

        return new ClassEntity($class['id'], $class['name'], $class['image']);
    }

    /**
     * @return ClassEntity[]|null
     */
    public function list(): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('SELECT * FROM class ORDER BY name');
        $db = null;

        if ($response === false) {
            return null;
        }

        $classes = [];
        foreach ($response as $class) {
            $classes[(int)$class['id']] = new ClassEntity($class['id'], $class['name'], $class['image']);
        }

        return $classes;
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        foreach ($this->list() as $class) {
            FileHandler::deleteFile($class->getImage());
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE class;');
        $db = null;

    }

}