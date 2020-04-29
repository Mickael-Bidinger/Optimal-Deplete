<?php

namespace App\services;

use App\entity\Dungeon;
use App\infrastructure\BlizzardApi;
use App\model\CurrentUpdateModel;
use App\model\RegionModel;
use App\repository\AffixRepository;
use App\repository\AffixSetRepository;
use App\repository\ClassRepository;
use App\repository\DungeonRepository;
use App\repository\FactionRepository;
use App\repository\LastUpdatedRepository;
use App\repository\LeaderboardRepository;
use App\repository\PeriodRepository;
use App\repository\RealmRepository;
use App\repository\RoleRepository;
use App\repository\SpecRepository;

class UpdatingService
{
    private $affixRepository;
    private $affixSetRepository;
    private $blizzardApi;
    private $currentUpdate;
    private $dungeonRepository;
    private $factionRepository;
    private $lastUpdatedRepository;
    private $leaderboardRepository;
    private $periodRepository;
    private $realmRepository;
    private $region;
    private $classRepository;
    private $roleRepository;
    private $specRepository;

    public function __construct()
    {
        $this->blizzardApi = new BlizzardApi();
        $this->affixRepository = new AffixRepository();
        $this->affixSetRepository = new AffixSetRepository();
        $this->classRepository = new ClassRepository();
        $this->currentUpdate = new CurrentUpdateModel();
        $this->dungeonRepository = new DungeonRepository();
        $this->factionRepository = new FactionRepository();
        $this->lastUpdatedRepository = new LastUpdatedRepository();
        $this->leaderboardRepository = new LeaderboardRepository();
        $this->periodRepository = new PeriodRepository();
        $this->realmRepository = new RealmRepository();
        $this->region = new RegionModel();
        $this->roleRepository = new RoleRepository();
        $this->specRepository = new SpecRepository();
    }

    public function run(int $limit = 0)
    {
        if (!$this->currentUpdate->start()) {
            return;
        }

        $currentSeason = $this->blizzardApi->findCurrentSeason();
        $isForcing = $this->currentUpdate->getIsForcing();
        $isReseting = $this->currentUpdate->getIsReseting();
        $lastUpdatedSeason = $this->currentUpdate->getSeason();

        if (\is_null($lastUpdatedSeason) || \is_null($currentSeason) || \is_null($isForcing) || \is_null($isReseting)) {
            return;
        }

        if ($lastUpdatedSeason === 0 || $lastUpdatedSeason !== $currentSeason) {
            $isReseting = true;
        }

        if ($isReseting || $isForcing) {
            $this
                ->reset($isReseting)
                ->updateRealms()
                ->updateSpecs()
                ->updateDungeons()
                ->updateAffixes()
                ->updateLastUpdated()
                ->currentUpdate->updateSeason($currentSeason)->resetIsForcing();
        }

        $this
            ->updatePeriods($currentSeason)
            ->updateLeaderboards($limit);
    }

    private function getAffixSetId(array &$affixSets, array $affixes): int
    {
        \usort($affixes, function ($a, $b) {
            return ($a['starting_level'] < $b['starting_level']) ? -1 : 1;
        });

        $affixSetString =
            $affixes[0]['keystone_affix']['id'] . '.' .
            $affixes[1]['keystone_affix']['id'] . '.' .
            $affixes[2]['keystone_affix']['id'] . '.' .
            $affixes[3]['keystone_affix']['id'];

        if (!\in_array($affixSetString, $affixSets)) {
            foreach ($affixes as $affix) {
                $this->affixRepository->updateStartingLevel($affix['keystone_affix']['id'], $affix['starting_level']);
            }
            $newAffixSet = $this->affixSetRepository->create(
                $affixes[0]['keystone_affix']['id'],
                $affixes[1]['keystone_affix']['id'],
                $affixes[2]['keystone_affix']['id'],
                $affixes[3]['keystone_affix']['id']
            );
            $affixSets[$newAffixSet->getId()] = $newAffixSet;
        }

        return \array_search($affixSetString, $affixSets);
    }

    private function getChest(int $duration, Dungeon $dungeon): int
    {
        if ($duration > $dungeon->getChest1()) {
            $chest = 0;
        } elseif ($duration > $dungeon->getChest2()) {
            $chest = 1;
        } elseif ($duration > $dungeon->getChest3()) {
            $chest = 2;
        } else {
            $chest = 3;
        }

        return $chest;

    }

    private function getDetailedMembers(array $members): array
    {
        foreach ($members as &$member) {
            $member = (int)$member['specialization']['id'];
        }

        return $members;
    }

    private function getFactionId(array &$factions, array $run): int
    {
        $factionName = 'NONE';
        foreach ($run['members'] as $member) {
            if (($faction = $member['faction']['type'] ?? 'NONE') !== 'NONE') {
                $factionName = \ucfirst(\mb_strtolower($faction, 'UTF-8'));
                break;
            }
        }
        if (!\in_array($factionName, $factions)) {
            $newFaction = $this->factionRepository->create($factionName);
            $factions[$newFaction->getId()] = $newFaction;
        }
        return \array_search($factionName, $factions);
    }

    private function getKeyLevel(?int &$min, ?int &$max, array $run): int
    {
        if (!isset($run['keystone_level'])) {
            return 0;
        }

        $keyLevel = $run['keystone_level'];

        if ($keyLevel > $max || \is_null($max)) {
            $max = $keyLevel;
        }
        if ($keyLevel < $min || \is_null($min)) {
            $min = $keyLevel;
        }
        return $keyLevel;
    }

    private function reset(bool $isReseting): self
    {
        $this->affixRepository->reset($isReseting);
        $this->affixSetRepository->reset($isReseting);
        $this->classRepository->reset($isReseting);
        $this->dungeonRepository->reset($isReseting);
        $this->factionRepository->reset($isReseting);
        $this->lastUpdatedRepository->reset($isReseting);
        $this->leaderboardRepository->reset($isReseting);
        $this->periodRepository->reset($isReseting);
        $this->realmRepository->reset($isReseting);
        $this->roleRepository->reset($isReseting);
        $this->specRepository->reset($isReseting);

        $this->currentUpdate->reset($isReseting);

        return $this;
    }

    private function updateAffixes(): self
    {
        $affixes = $this->affixRepository->list();

        foreach ($this->blizzardApi->listAffixes() as $affix) {
            if (\array_key_exists($affix['id'], $affixes)) {
                continue;
            }
            $affixInfo = $this->blizzardApi->findAffixInfo($affix['id']);
            $affixes[(int)$affix['id']] = $this->affixRepository->create($affix['id'], $affixInfo['name'], $affixInfo['image']);
        }

        return $this;
    }

    private function updateDungeons(): self
    {
        $dungeons = $this->dungeonRepository->list();

        foreach ($this->blizzardApi->listDungeons() as $dungeon) {
            if (\array_key_exists($dungeon['id'], $dungeons)) {
                continue;
            }
            $dungeonInfo = $this->blizzardApi->findDungeonInfo($dungeon['id']);
            $dungeons[(int)$dungeon['id']] = $this->dungeonRepository->create(
                $dungeon['id'],
                $dungeon['name'],
                $dungeonInfo['image'],
                $dungeonInfo['chests'][0],
                $dungeonInfo['chests'][1],
                $dungeonInfo['chests'][2]
            );
        }
        return $this;
    }

    private function updateLastUpdated(): self
    {
        $lastUpdatedList = $this->lastUpdatedRepository->list(0);

        foreach ($this->realmRepository->list() as $realmId => $realm) {
            foreach ($this->dungeonRepository->list() as $dungeonId => $dungeon) {
                $regionId = $realm->getRegionId();
                $lastUpdatedString = "$dungeonId.$realmId.$regionId";
                if (\in_array($lastUpdatedString, $lastUpdatedList)) {
                    continue;
                }
                $newLastUpdated = $this->lastUpdatedRepository->create($dungeonId, $realmId, $regionId);
                $lastUpdatedList[$newLastUpdated->getId()] = $newLastUpdated;
            }
        }

        return $this;
    }

    private function updateLeaderboards(int $limit): self
    {
        $affixSets = $this->affixSetRepository->list();
        $dungeons = $this->dungeonRepository->list();
        $factions = $this->factionRepository->list();
        $keyLevelMax = $this->currentUpdate->getKeyLevelMax();
        $keyLevelMin = $this->currentUpdate->getKeyLevelMin();
        $periods = $this->periodRepository->list();
        $regions = $this->region->list();

        foreach ($this->lastUpdatedRepository->list($limit) as $lastUpdatedId => $lastUpdated) {
            $dungeonId = $lastUpdated->getDungeonId();
            $realmId = $lastUpdated->getRealmId();
            $region = $regions[$lastUpdated->getRegionId()];

            foreach ($periods as $periodId => $period) {
                if ($lastUpdated->getLastPeriod() > $periodId) {
                    continue;
                }

                $leaderboards = $this->blizzardApi->listLeaderboards($region, $realmId, $dungeonId, $periodId);

                if (
                    \is_null($leaderboards)
                    ||
                    (isset($leaderboards['code']) && $leaderboards['code'] !== 200)
                    ||
                    !isset($leaderboards['leading_groups'])
                ) {
                    break;
                }

                $affixSetId = $this->getAffixSetId($affixSets, $leaderboards['keystone_affixes']);
                $dungeon = $dungeons[$dungeonId];
                $lastDungeonTime = $lastUpdated->getLastDungeon();
                $currentLastDungeonTime = $lastDungeonTime;
                foreach ($leaderboards['leading_groups'] as $run) {
                    if ($run['completed_timestamp'] < $lastDungeonTime) {
                        continue;
                    }
                    if ($run['completed_timestamp'] > $currentLastDungeonTime) {
                        $currentLastDungeonTime = $run['completed_timestamp'];
                    }

                    $keyLevel = $this->getKeyLevel($keyLevelMin, $keyLevelMax, $run);
                    $factionId = $this->getFactionId($factions, $run);
                    $chest = $this->getChest($run['duration'], $dungeon);
                    $members = $this->getDetailedMembers($run['members']);

                    $this->leaderboardRepository->buffer([
                        'affix' => $affixSetId,
                        'chest' => $chest,
                        'dungeon' => $dungeonId,
                        'faction' => $factionId,
                        'level' => $keyLevel,
                        'specs' => $members,
                    ]);
                }

                $attempts = 0;
                do {
                    try {
                        $this->leaderboardRepository->flush();
                        $this->lastUpdatedRepository->update($lastUpdatedId, $periodId, $currentLastDungeonTime);
                        $this->currentUpdate->updateKeyLevelMinMax($keyLevelMin, $keyLevelMax);
                    } catch (\PDOException $PDOException) {
                        if (++$attempts === 10) {
                            return $this;
                        }
                        sleep(10);
                        continue;
                    }
                    break;
                } while ($attempts < 10);
            }
        }

        return $this;
    }

    private function updatePeriods(int $currentSeason): self
    {
        $periods = $this->periodRepository->list();

        foreach ($this->blizzardApi->listPeriodsBySeason($currentSeason) as $period) {
            if (\array_key_exists($period['id'], $periods)) {
                continue;
            }
            $periods[(int)$period['id']] = $this->periodRepository->create($period['id']);
        }

        return $this;
    }

    private function updateRealms(): self
    {
        $realms = $this->realmRepository->list();
        $regions = $this->region->list();

        foreach ($regions as $regionId => $region) {
            foreach ($this->blizzardApi->listRealmsByRegion($region) as $realm) {
                if (\array_key_exists($realm, $realms)) {
                    continue;
                }
                $realms[(int)$realm] = $this->realmRepository->create($realm, $regionId);
            }
        }

        return $this;
    }

    private function updateSpecs(): self
    {
        $classes = $this->classRepository->list();
        $roles = $this->roleRepository->list();
        $specs = $this->specRepository->list();

        foreach ($this->blizzardApi->listSpecs() as $spec) {
            if (\array_key_exists($spec['id'], $specs)) {
                continue;
            }

            $spec = $this->blizzardApi->findSpec($spec['id']);
            if (!\array_key_exists($spec['playable_class']['id'], $classes)) {
                $classes[(int)$spec['playable_class']['id']] = $this->classRepository->create(
                    $spec['playable_class']['id'],
                    $spec['playable_class']['name'],
                    $this->blizzardApi->findClassImage($spec['playable_class']['id'])
                );
            }

            if (!\in_array($spec['role']['name'], $roles)) {
                $newRole = $this->roleRepository->create($spec['role']['name']);
                $roles[$newRole->getId()] = $newRole;
            }

            $roleId = \array_search($spec['role']['name'], $roles);

            $specs[(int)$spec['id']] = $this->specRepository->create(
                $spec['id'],
                $spec['playable_class']['id'],
                $roleId,
                "{$spec['name']}-{$spec['playable_class']['name']}",
                $this->blizzardApi->findSpecImage($spec['id'])
            );
        }
        return $this;
    }

}