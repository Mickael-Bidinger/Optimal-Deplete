<?php

namespace App\repository;


use MB\Database;

class LeaderboardStatRepository
{
    private $queue = ['', '', '', ''];

    public function buffer(array $leaderboard)
    {
        $affixes = [0, $leaderboard['affix']];
        $chest = $leaderboard['chest'];
        $dungeons = [0, $leaderboard['dungeon']];
        $factions = [0, $leaderboard['faction']];
        $levels = [0, $leaderboard['level']];
        $specs = \array_unique(\array_merge([0], $leaderboard['specs']));

        foreach ($affixes as $affix) {
            foreach ($dungeons as $dungeon) {
                foreach ($factions as $faction) {
                    foreach ($levels as $level) {
                        foreach ($specs as $spec) {
                            $this->queue[$chest] .= PHP_EOL . "($affix, $dungeon, $faction, $level, $spec, 1, 1),";
                        }
                    }
                }
            }
        }

    }

    public function clear()
    {
        $this->queue = ['', '', '', ''];
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

    public function flush(int $lastId)
    {
        if ($this->queue === ['', '', '', '']) {
            return;
        }
        foreach ($this->queue as $chest => &$sql) {
            if ($sql === '') continue;
            $sql = \rtrim($sql, ",");
            $sql = "
                INSERT INTO leaderboard_stats
                  (affix, dungeon, faction, level, specialization, chest_$chest, total)
                VALUES $sql
                ON DUPLICATE KEY UPDATE chest_$chest = chest_$chest + 1, total = total + 1;";
        }

        $db = new Database();
        $db->execute('
            START TRANSACTION;' .
            \implode($this->queue) . "
            UPDATE current_update SET last_leaderboard_id = $lastId;
            COMMIT;"
        );
        $db = null;
        $this->queue = ['', '', '', ''];
    }

    public function reset(bool $confirm = false)
    {
        if (!$confirm) {
            return;
        }
        $db = new Database();
        $db->execute('SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE leaderboard_stats;');
        $db = null;
    }

}