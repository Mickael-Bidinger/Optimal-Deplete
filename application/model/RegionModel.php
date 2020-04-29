<?php

namespace App\model;


use MB\Database;

class RegionModel
{
    private $list = [];

    public function __construct()
    {
        $db = new Database();
        $response = $db->queryMultiple('SELECT * FROM region');
        $db = null;

        if ($response === false) {
            $this->list = null;
            return;
        }

        foreach ($response as $region) {
            $this->list[(int)$region['id']] = $region['name'];
        }
    }

    public function list(): ?array
    {
        return $this->list;
    }

}