<?php

namespace App\model;


use MB\Database;

class CurrentUpdateModel
{
    public function getIsForcing(): ?bool
    {
        $db = new Database();
        $response = $db->queryOne('SELECT is_forcing FROM current_update');
        $db = null;

        if ($response === false) {
            return null;
        }

        return $response['is_forcing'];
    }

    public function getIsReseting(): ?bool
    {
        $db = new Database();
        $response = $db->queryOne('SELECT is_reseting FROM current_update');
        $db = null;

        if ($response === false) {
            return null;
        }

        return $response['is_reseting'];
    }

    public function getKeyLevelMax(): ?int
    {
        $db = new Database();
        $response = $db->queryOne('SELECT key_level_max FROM current_update');
        $db = null;

        if ($response === false) {
            return null;
        }

        return $response['key_level_max'];
    }

    public function getKeyLevelMin(): ?int
    {
        $db = new Database();
        $response = $db->queryOne('SELECT key_level_min FROM current_update');
        $db = null;

        if ($response === false) {
            return null;
        }

        return $response['key_level_min'];
    }

    public function getSeason(): ?int
    {
        $db = new Database();
        $response = $db->queryOne('SELECT season FROM current_update');
        $db = null;

        if ($response === false) {
            return null;
        }

        return $response['season'];
    }

    public function reset(bool $confirm = false): self
    {
        if (!$confirm) {
            return $this;
        }

        $db = new Database();
        $db->execute('
            UPDATE current_update 
            SET key_level_max = NULL,
                key_level_min = NULL,
                is_reseting = 0
        ');
        $db = null;

        return $this;
    }

    public function resetIsForcing(): self
    {
        $db = new Database();
        $db->execute('UPDATE current_update SET is_forcing = 0');
        $db = null;

        return $this;
    }

    public function setIsForcingIsReseting(bool $isForcing, bool $isReseting): self
    {
        $db = new Database();
        $db->execute(
            'UPDATE current_update SET is_forcing = :is_forcing, is_reseting = :is_reseting',
            [':is_forcing' => (int)$isForcing, ':is_reseting' => (int)$isReseting]
        );
        $db = null;

        return $this;
    }

    public function start(): bool
    {

        $db = new Database();
        $rowCount = $db->executeGetRowCount("
            UPDATE current_update 
            SET 
                is_running = 
                    CASE 
                        WHEN @cond := (is_running = 0 OR is_running_since < :two_days_ago) THEN 1
                        ELSE is_running
                    END,
                is_running_since = 
                    CASE
                        WHEN @cond THEN :now
                        ELSE is_running_since
                    END;
            ", [
                ':now' => \date_create()->format('Y-m-d H:i:s'),
                ':two_days_ago' => \date_create('-2 day')->format('Y-m-d H:i:s'),
            ]
        );
        $db = null;

        if ($rowCount === 0) {
            return false;
        }

        \register_shutdown_function([$this, 'stop']);

        return true;
    }

    public function stop(): self
    {
        $db = new Database();
        $db->execute('UPDATE current_update SET is_running = 0');
        $db = null;

        return $this;
    }

    public function updateSeason(int $season): self
    {
        $db = new Database();
        $db->execute('UPDATE current_update SET season = ?', [$season]);
        $db = null;

        return $this;
    }

    public function updateKeyLevelMinMax(int $keyLevelMin, int $keyLevelMax): self
    {
        $db = new Database();
        $db->execute(
            'UPDATE current_update SET key_level_max = :key_level_max, key_level_min = :key_level_min',
            [
                ':key_level_max' => $keyLevelMax,
                ':key_level_min' => $keyLevelMin,
            ]
        );
        $db = null;

        return $this;
    }

}