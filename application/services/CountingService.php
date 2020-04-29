<?php

namespace App\services;

use App\model\CurrentUpdateModel;
use App\repository\AffixSetRepository;
use App\repository\DungeonRepository;
use App\repository\FactionRepository;
use App\repository\LeaderboardRepository;
use App\repository\SpecRepository;

class CountingService
{
    private $affixSets;
    private $dungeons;
    private $factions;
    private $leaderboardRepository;
    private $levels;
    private $sortings = ['specialization', 'dungeon', 'level', 'faction', 'affix', 'none'];
    private $specs;

    public function __construct()
    {
        $this->leaderboardRepository = new LeaderboardRepository();

        $this->affixSets = \array_keys((new AffixSetRepository())->list() ?? []);
        $this->dungeons = \array_keys((new DungeonRepository())->list() ?? []);
        $this->specs = \array_keys((new SpecRepository())->list() ?? []);

        foreach ((new FactionRepository())->list() ?? [] as $factionId => $faction) {
            if ($faction->getName() === 'NONE') {
                continue;
            }
            $this->factions[] = $factionId;
        }

        $currentUpdate = new CurrentUpdateModel();
        $max = $currentUpdate->getKeyLevelMax() ?? 0;
        for ($min = $currentUpdate->getKeyLevelMin() ?? 0; $min <= $max; $this->levels[] = $min++) ;

    }

    public function run(array $filters, string $sorting): array
    {
        if ($this->areListsEmpty()) {
            return [
                'counts' => [],
                'total' => [],
                'sorting' => $sorting,
                'sortings' => $this->sortings
            ];
        }

        $sql = $this->getSql($filters, 'none');
        $sorting = $this->getSorting($sorting);

        $result = [
            'counts' => [],
            'total' => $this->leaderboardRepository->count($sql),
            'sorting' => $sorting,
            'sortings' => $this->sortings
        ];

        if ($sorting !== 'none') {
            $sql = $this->getSql($filters, $sorting);
            $result['counts'] = $this->leaderboardRepository->count($sql);
        }

        return $result;
    }

    private function areListsEmpty(): bool
    {
        return
            empty($this->affixSets) ||
            empty($this->dungeons) ||
            empty($this->factions) ||
            empty($this->levels) ||
            empty($this->specs);
    }

    private function getSql(array $filters, string $sorting): string
    {
        // ok, c'est de l'injection sql, mais tous les params du where sont passés par des (int)$val
        // c'est donc safe selon plusieurs sources web ET Yann :p
        // pour ce qui est de SELECT et GROUP BY, les values viennent forcement de $this->sortings
        // c'est donc safe également :)
        return "
            SELECT 
                {$this->getSelect($sorting)}
                SUM(chest_0) as \"0\",
                SUM(chest_1) as \"1\",
                SUM(chest_2) as \"2\",
                SUM(chest_3) as \"3\",
                SUM(total) as total,
                (SUM(chest_0) / SUM(total)) as percent
            FROM leaderboard
            WHERE 
                affix {$this->getAffixes($filters, $sorting)} AND
                dungeon {$this->getDungeons($filters, $sorting)} AND
                faction {$this->getFactions($filters, $sorting)} AND
                level {$this->getLevels($filters, $sorting)} AND
                specialization {$this->getSpecs($filters, $sorting)}
            {$this->getGroup($sorting)}
            {$this->getOrderBy($sorting)}";
    }

    private function getAffixes(array $filters, string $sorting): string
    {
        if ($sorting === 'affix') {
            $affixes = \implode(', ', $this->affixSets);
            return "IN ($affixes)";
        }

        $validAffixes = [];

        foreach ($filters['affix'] ?? [] as $affix) {
            if (\in_array($affix, $this->affixSets)) {
                $validAffixes[] = (int)$affix;
            }
        }

        if (empty($validAffixes)) {
            return '= 0';
        }

        $this->affixSets = $validAffixes;

        if (\count($validAffixes) === 1) {
            if (($index = \array_search('affix', $this->sortings)) !== false) {
                unset($this->sortings[$index]);
            }
            return "= $validAffixes[0]";
        }

        $affixes = \implode(', ', $validAffixes);
        return "IN ($affixes)";
    }

    private function getDungeons(array $filters, string $sorting): string
    {
        if ($sorting === 'dungeon') {
            $dungeons = \implode(', ', $this->dungeons);
            return "IN ($dungeons)";
        }

        $validDungeons = [];

        foreach ($filters['dungeon'] ?? [] as $dungeon) {
            if (\in_array($dungeon, $this->dungeons)) {
                $validDungeons[] = (int)$dungeon;
            }
        }

        if (empty($validDungeons)) {
            return '= 0';
        }

        $this->dungeons = $validDungeons;

        if (\count($validDungeons) === 1) {
            if (($index = \array_search('dungeon', $this->sortings)) !== false) {
                unset($this->sortings[$index]);
            }
            return "= $validDungeons[0]";
        }

        $dungeons = \implode(', ', $validDungeons);
        return "IN ($dungeons)";
    }

    private function getFactions(array $filters, string $sorting): string
    {
        if ($sorting === 'faction') {
            $factions = \implode(', ', $this->factions);
            return "IN ($factions)";
        }

        $validFactions = [];

        foreach ($filters['faction'] ?? [] as $faction) {
            if (\in_array($faction, $this->factions)) {
                $validFactions[] = (int)$faction;
            }
        }

        if (empty($validFactions)) {
            return '= 0';
        }

        $this->factions = $validFactions;

        if (\count($validFactions) === 1) {
            if (($index = \array_search('faction', $this->sortings)) !== false) {
                unset($this->sortings[$index]);
            }
            return "= $validFactions[0]";
        }

        $factions = \implode(', ', $validFactions);
        return "IN ($factions)";
    }

    private function getGroup(string $sorting): string
    {
        return $sorting === 'none' ? '' : "GROUP BY $sorting";
    }

    private function getOrderBy(string $sorting): string
    {
        return $sorting === 'level' ? 'ORDER BY _id' : "ORDER BY percent";
    }

    private function getLevels(array $filters, string $sorting): string
    {
        if ($sorting === 'level') {
            $levels = \implode(', ', $this->levels);
            return "IN ($levels)";
        }

        $validLevels = [];

        foreach ($filters['level'] ?? [] as $level) {
            if (\in_array($level, $this->levels)) {
                $validLevels[] = (int)$level;
            }
        }

        if (empty($validLevels)) {
            return '= 0';
        }

        $this->levels = $validLevels;

        if (\count($validLevels) === 1) {
            if (($index = \array_search('level', $this->sortings)) !== false) {
                unset($this->sortings[$index]);
            }
            return "= $validLevels[0]";
        }

        $levels = \implode(', ', $validLevels);
        return "IN ($levels)";
    }

    private function getSelect(string $sorting): string
    {
        return $sorting === 'none' ? '' : "$sorting as _id,";
    }

    private function getSorting(string $sorting): string
    {
        if (!\in_array($sorting, $this->sortings)) {
            return $this->sortings[\array_key_first($this->sortings)];
        }
        return $sorting;
    }

    private function getSpecs(array $filters, string $sorting): string
    {
        if ($sorting === 'specialization') {
            $specs = \implode(', ', $this->specs);
            return "IN ($specs)";
        }

        $validSpecs = [];

        foreach ($filters['specialization'] ?? [] as $spec) {
            if (\in_array($spec, $this->specs)) {
                $validSpecs[] = (int)$spec;
            }
        }

        if (empty($validSpecs)) {
            return '= 0';
        }

        $this->specs = $validSpecs;

        if (\count($validSpecs) === 1) {
            if (($index = \array_search('specialization', $this->sortings)) !== false) {
                unset($this->sortings[$index]);
            }
            return "= $validSpecs[0]";
        }

        $specs = \implode(', ', $validSpecs);
        return "IN ($specs)";
    }

}