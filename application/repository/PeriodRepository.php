<?php

namespace App\repository;


use App\entity\Period;
use MB\Database;

class PeriodRepository
{

    public function create(int $id): Period
    {
        $db = new Database();
        $db->execute('INSERT INTO period SET id = ?', [$id]);
        $db = null;

        return new Period($id);
    }

    /**
     * @return Period[]|null
     */
    public function list(): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('SELECT id FROM period ORDER BY id ASC');
        $db = null;

        if ($response === false) {
            return null;
        }

        $periods = [];
        foreach ($response as $period) {
            $periods[(int)$period['id']] = new Period($period['id']);
        }

        return $periods;
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE period;');
        $db = null;

    }

}