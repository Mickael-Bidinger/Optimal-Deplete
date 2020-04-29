<?php

namespace App\repository;


use App\entity\Spec;
use MB\Database;
use MB\FileHandler;

class SpecRepository
{
    private const IMAGE_WIDTH = 35;
    private const IMAGE_HEIGHT = 35;

    public function create(int $id, int $classId, int $roleId, string $name, string $imageUrl): Spec
    {
        $imageLocal = \mb_strtolower("images/spec/$name.png", 'UTF-8');
        $imageLocal = \mb_ereg_replace(':', '', $imageLocal);
        $imageLocal = \mb_ereg_replace(' ', '_', $imageLocal);

        FileHandler::resizeImage($imageUrl, $imageLocal, self::IMAGE_WIDTH, self::IMAGE_HEIGHT);

        $db = new Database();
        $db->execute('
            INSERT INTO spec
            (id, class_id, role_id, name, image)
            VALUES
            (:id, :class_id, :role_id, :name, :image)
        ', [
            ':id' => $id,
            ':class_id' => $classId,
            ':role_id' => $roleId,
            ':name' => $name,
            ':image' => $imageLocal,
        ]);
        $db = null;

        return new Spec($id, $classId, $roleId, $name, $imageLocal);
    }

    public function find(int $id): ?Spec
    {
        $db = new Database();
        $response = $db->queryOne('
                SELECT
                    id,
                    class_id,
                    image,
                    name,
                    role_id
                FROM spec
                WHERE id = ?
           ', [$id]);
        $db = null;

        if ($response === false) {
            return null;
        }

        return new Spec(
            $response['id'],
            $response['class_id'],
            $response['role_id'],
            $response['name'],
            $response['image']
        );
    }

    /**
     * @return Spec[]|null
     */
    public function list(): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('
                SELECT
                    spec.id,
                    class_id,
                    role_id,
                    spec.name,
                    spec.image
                FROM spec        
                JOIN class ON class_id = class.id
                JOIN role ON role_id = role.id
                ORDER BY class.name, role.name DESC, name
           ');
        $db = null;

        if ($response === false) {
            return null;
        }

        $specs = [];
        foreach ($response as $spec) {
            $specs[(int)$spec['id']] = new Spec(
                $spec['id'],
                $spec['class_id'],
                $spec['role_id'],
                $spec['name'],
                $spec['image']
            );
        }

        return $specs;
    }

    /**
     * @param int $class
     * @return Spec[]|null
     */
    public function listByClass(int $class): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('
                SELECT
                    spec.id,
                    class_id,
                    role_id,
                    spec.name,
                    spec.image
                FROM spec
                JOIN class ON class_id = class.id
                JOIN role ON role_id = role.id
                WHERE class_id = ?
                ORDER BY class.name, role.name DESC, name
           ', [$class]);
        $db = null;

        if ($response === false) {
            return null;
        }

        $specs = [];
        foreach ($response as $spec) {
            $specs[$spec['id']] = new Spec(
                $spec['id'],
                $spec['class_id'],
                $spec['role_id'],
                $spec['name'],
                $spec['image']
            );
        }

        return $specs;
    }

    /**
     * @param int $role
     * @return Spec[]|null
     */
    public function listByRole(int $role): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('
                SELECT
                    spec.id,
                    class_id,
                    role_id,
                    spec.name,
                    spec.image
                FROM spec        
                JOIN class ON class_id = class.id
                JOIN role ON role_id = role.id
                WHERE role_id = ?
                ORDER BY class.name, role.name DESC, name
           ', [$role]);
        $db = null;

        if ($response === false) {
            return null;
        }

        $specs = [];
        foreach ($response as $spec) {
            $specs[$spec['id']] = new Spec(
                $spec['id'],
                $spec['class_id'],
                $spec['role_id'],
                $spec['name'],
                $spec['image']
            );
        }

        return $specs;
    }

    public function listIdsByClass(int $class): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('
                SELECT
                    id
                FROM spec
                WHERE class_id = ?
                ORDER BY id
           ', [$class]);
        $db = null;

        if ($response === false) {
            return null;
        }

        return \array_column($response, 'id');
    }

    public function listIdsByRole(int $role): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('
                SELECT
                    id
                FROM spec
                WHERE role_id = ?
                ORDER BY id
           ', [$role]);
        $db = null;

        if ($response === false) {
            return null;
        }

        return \array_column($response, 'id');
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        foreach ($this->list() as $spec) {
            FileHandler::deleteFile($spec->getImage());
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE spec;');
        $db = null;

    }

}