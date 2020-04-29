<?php

namespace App\repository;


use MB\Database;
use PDOException;

class LeaderboardRepository
{
    private $queue = [];

    public function buffer(array $leaderboard)
    {
        $affixes = [0, $leaderboard['affix']];
        $chest = $leaderboard['chest'];
        $dungeons = [0, $leaderboard['dungeon']];
        $factions = [0, $leaderboard['faction']];
        $levels = \array_unique([0, $leaderboard['level']]);
        $specs = \array_unique(\array_merge([0], $leaderboard['specs']));

        if (!isset($this->queue[$chest])) {
            $this->queue[$chest] = "
                INSERT INTO leaderboard
                  (id, affix, dungeon, faction, level, specialization, chest_$chest, total)
                VALUES";
        }

        foreach ($affixes as $affix) {
            foreach ($dungeons as $dungeon) {
                foreach ($factions as $faction) {
                    foreach ($levels as $level) {
                        foreach ($specs as $spec) {
                            $id = "{$affix}_{$dungeon}_{$faction}_{$level}_{$spec}";
                            // ok, c'est de l'injection sql, mais tous les params sont passés par des (int)$val
                            // c'est donc safe selon plusieurs sources web ET Yann :p
                            $this->queue[$chest] .= PHP_EOL . "(\"$id\", $affix, $dungeon, $faction, $level, $spec, 1, 1),";
                        }
                    }
                }
            }
        }

    }

    public function count(string $sql, array $values = []): ?array
    {
        $db = new Database();
        $counts = $db->queryMultiple($sql, $values);
        $db = null;

        if ($counts === false) {
            return null;
        }

        if (empty($counts) || \is_null($counts[0]['total'])) {
            return [];
        }

        return $counts;
    }

    public function flush()
    {
        if (empty($this->queue)) {
            return;
        }
        foreach ($this->queue as $chest => &$sql) {
            $sql = \rtrim($sql, ",");
            $sql .= PHP_EOL . "ON DUPLICATE KEY UPDATE chest_$chest = chest_$chest + 1, total = total + 1;";
        }

        $db = new Database();
        $db->execute(\implode(PHP_EOL, $this->queue));
        $db = null;
        $this->queue = [];
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE leaderboard;');
        $db = null;
    }

}