<?php

namespace App\repository;


use App\entity\AffixSet;
use MB\Database;

class AffixSetRepository
{
    private const IMAGE_WIDTH = 40;
    private const IMAGE_HEIGHT = 40;

    public function create(int $affix1Id, int $affix2Id, int $affix3Id, int $affix4Id): ?AffixSet
    {
        $db = new Database();
        $lastId = $db->execute('
            INSERT INTO affix_set
            (affix1_id, affix2_id, affix3_id, affix4_id)
            VALUES
            (:affix1_id, :affix2_id, :affix3_id, :affix4_id)
        ', [
            ':affix1_id' => $affix1Id,
            ':affix2_id' => $affix2Id,
            ':affix3_id' => $affix3Id,
            ':affix4_id' => $affix4Id,
        ]);
        $db = null;

        return $this->find($lastId);
    }

    public function find(int $id): ?AffixSet
    {
        $db = new Database();
        $affixSet = $db->queryOne('
            SELECT
                affix_set.id AS affix_set_id,
                affix1.id AS affix1_id,
                affix1.image AS affix1_image,
                affix1.name AS affix1_name,
                affix1.starting_level AS affix1_starting_level,
                affix2.id AS affix2_id,
                affix2.image AS affix2_image,
                affix2.name AS affix2_name,
                affix2.starting_level AS affix2_starting_level,
                affix3.id AS affix3_id,
                affix3.image AS affix3_image,
                affix3.name AS affix3_name,
                affix3.starting_level AS affix3_starting_level,
                affix4.id AS affix4_id,
                affix4.image AS affix4_image,
                affix4.name AS affix4_name,
                affix4.starting_level AS affix4_starting_level
            FROM affix_set
            JOIN affix AS affix1 ON affix1_id = affix1.id
            JOIN affix AS affix2 ON affix2_id = affix2.id
            JOIN affix AS affix3 ON affix3_id = affix3.id
            JOIN affix AS affix4 ON affix4_id = affix4.id
            WHERE affix_set.id = ?
        ',
            [$id]
        );
        $db = null;

        if ($affixSet === false || empty($affixSet)) {
            return null;
        }

        return new AffixSet($affixSet['affix_set_id'], $affixSet);
    }

    /**
     * @return AffixSet[]|null
     */
    public function list(): ?array
    {
        $db = new Database();
        $response = $db->queryMultiple('
            SELECT
                affix_set.id AS affix_set_id,
                affix1.id AS affix1_id,
                affix1.image AS affix1_image,
                affix1.name AS affix1_name,
                affix1.starting_level AS affix1_starting_level,
                affix2.id AS affix2_id,
                affix2.image AS affix2_image,
                affix2.name AS affix2_name,
                affix2.starting_level AS affix2_starting_level,
                affix3.id AS affix3_id,
                affix3.image AS affix3_image,
                affix3.name AS affix3_name,
                affix3.starting_level AS affix3_starting_level,
                affix4.id AS affix4_id,
                affix4.image AS affix4_image,
                affix4.name AS affix4_name,
                affix4.starting_level AS affix4_starting_level
            FROM affix_set
            JOIN affix AS affix1 ON affix1_id = affix1.id
            JOIN affix AS affix2 ON affix2_id = affix2.id
            JOIN affix AS affix3 ON affix3_id = affix3.id
            JOIN affix AS affix4 ON affix4_id = affix4.id'
        );
        $db = null;

        if ($response === false) {
            return null;
        }

        $affixSets = [];
        foreach ($response as $affixSet) {
            $affixSets[(int)$affixSet['affix_set_id']] = new AffixSet($affixSet['affix_set_id'], $affixSet);
        }

        return $affixSets;
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }

        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE affix_set;');
        $db = null;
    }


}