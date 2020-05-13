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

    public function clear()
    {
        $this->queue = '';
    }

    public function flush(int $id, int $lastPeriod, int $lastDungeon, int $keyLevelMin, int $keyLevelMax)
    {
        $date = date_create()->format('Y-m-d H:i:s');
        if ($this->queue === '') {
            $db = new Database();
            $db->execute("UPDATE last_updated SET last_update_date = '$date' WHERE id = $id;");
            $db = null;
            return;
        }
        $this->queue = \rtrim($this->queue, ",");
        $this->queue = "
            START TRANSACTION;
            
            INSERT INTO leaderboard 
                (affix, chest, completed_timestamp, dungeon, faction, level, member_1, member_2, member_3, member_4, member_5)
            VALUES $this->queue
            ON DUPLICATE KEY UPDATE id=id;
                
            UPDATE last_updated 
            SET last_period = $lastPeriod,
                last_dungeon = $lastDungeon,
                last_update_date = '$date'
            WHERE id = $id;
                
            UPDATE current_update SET key_level_max = $keyLevelMax, key_level_min = $keyLevelMin, last_modified = '$date';
            
            COMMIT;";

        $db = new Database();
        $db->execute($this->queue);
        $db = null;
        $this->queue = '';
    }

    public function list(int $fromId = 0, int $limit = 0): array
    {
        $limit = $limit === 0 ? '' : "LIMIT $limit";
        $db = new Database();
        $response = $db->queryMultiple("
            SELECT *
            FROM leaderboard
            WHERE id > $fromId
            ORDER BY id
            $limit 
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