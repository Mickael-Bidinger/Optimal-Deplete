<?php

namespace App\repository;


use MB\Database;

class LeaderboardRepository
{
    private $queue = '';

    public function buffer(array $leaderboard)
    {
        $this->queue .= "
            ({$leaderboard['affix']},
             {$leaderboard['chest']},
             {$leaderboard['completed']},
             {$leaderboard['dungeon']},
             {$leaderboard['faction']},
             {$leaderboard['level']},
             {$leaderboard['specs'][0]},
             {$leaderboard['specs'][1]},
             {$leaderboard['specs'][2]},
             {$leaderboard['specs'][3]},
             {$leaderboard['specs'][4]}),";
    }

    public function flush()
    {
        if ($this->queue === '') {
            return;
        }
        $this->queue = \rtrim($this->queue, ",");
        $this->queue = "
            INSERT IGNORE INTO leaderboard
              (affix, chest, completed_timestamp, dungeon, faction, level, member_1, member_2, member_3, member_4, member_5)
            VALUES $this->queue;";

        $db = new Database();
        $db->execute($this->queue);
        $db = null;
        $this->queue = '';
    }

    public function list(int $fromId = 0): array
    {
        $db = new Database();
        $response = $db->queryMultiple("
            SELECT *
            FROM leaderboard
            WHERE id > $fromId
            ORDER BY id
        ");
        $db = null;

        if ($response === false) {
            return [];
        }

        return $response;
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