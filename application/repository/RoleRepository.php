<?php

namespace App\repository;


use App\entity\Role;
use MB\Database;

class RoleRepository
{

    public function create(string $name): Role
    {
        $image = \mb_strtolower("images/role/$name.png", 'UTF-8');

        $db = new Database();
        $id = $db->execute('
            INSERT INTO role
            (name, image)
            VALUES
            (:name, :image)
        ', [
            ':name' => $name,
            ':image' => $image,
        ]);
        $db = null;

        return new Role($id, $name, $image);
    }

    public function find(int $id): ?Role
    {
        $db = new Database();
        $role = $db->queryOne('
                SELECT
                    id,
                    name,
                    image
                FROM role
                WHERE id = ?           
           ', [$id]);
        $db = null;

        if ($role === false || empty($role)) {
            return null;
        }

        return new Role($role['id'], $role['name'], $role['image']);
    }

    public function findOneByName(string $name): ?Role
    {
        $db = new Database();
        $role = $db->queryOne('
                SELECT
                    id,
                    name,
                    image
                FROM role
                WHERE name = ?           
           ', [$name]);
        $db = null;

        if ($role === false || empty($role)) {
            return null;
        }

        return new Role($role['id'], $role['name'], $role['image']);
    }

    /**
     * @return Role[]|null
     */
    public function list(): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('
                SELECT
                    id,
                    name,
                    image
                FROM role
                ORDER BY name DESC
           ');
        $db = null;

        if ($response === false) {
            return null;
        }

        $roles = [];
        foreach ($response as $role) {
            $roles[(int)$role['id']] = new Role($role['id'], $role['name'], $role['image']);
        }

        return $roles;
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE role;');
        $db = null;

    }

}