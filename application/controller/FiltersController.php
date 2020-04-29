<?php

namespace App\controller;

use App\model\CurrentUpdateModel;
use App\repository\AffixSetRepository;
use App\repository\ClassRepository;
use App\repository\DungeonRepository;
use App\repository\FactionRepository;
use App\repository\RoleRepository;
use App\repository\SpecRepository;
use MB\ControllerInterface;

/**
 * Class HomeController
 * @package App\controller
 */
class FiltersController implements ControllerInterface
{

    public function index(array $parameters): array
    {
        return [];
    }

    /**
     * @subRoute("affix")
     * @param array $parameters
     * @return array
     */
    public function affix(array $parameters): array
    {
        return [
            'affixSetList' => (new AffixSetRepository())->list() ?? [],
            '_layoutUsed' => false,
            '_view' => 'body/options/Affix',
        ];
    }

    /**
     * @subRoute("dungeon")
     * @param array $parameters
     * @return array
     */
    public function dungeon(array $parameters): array
    {
        return [
            'dungeons' => (new DungeonRepository())->list() ?? [],
            '_layoutUsed' => false,
            '_view' => 'body/options/Dungeon',
        ];
    }

    /**
     * @subRoute("faction")
     * @param array $parameters
     * @return array
     */
    public function faction(array $parameters): array
    {
        return [
            'factions' => (new FactionRepository())->list() ?? [],
            '_layoutUsed' => false,
            '_view' => 'body/options/Faction',
        ];
    }

    /**
     * @subRoute("level")
     * @param array $parameters
     * @return array
     */
    public function level(array $parameters): array
    {
        $currentUpdate = new CurrentUpdateModel();

        return [
            'keyLevelMax' => $currentUpdate->getKeyLevelMax() ?? 0,
            'keyLevelMin' => $currentUpdate->getKeyLevelMin() ?? 0,
            '_layoutUsed' => false,
            '_view' => 'body/options/Level',
        ];
    }

    /**
     * @subRoute("specialization")
     * @param array $parameters
     * @return array
     */
    public function specialization(array $parameters): array
    {
        return [
            'classes' => (new ClassRepository)->list() ?? [],
            'roles' => (new RoleRepository)->list() ?? [],
            'specRepo' => new SpecRepository(),
            '_view' => 'body/options/Spec',
            '_layoutUsed' => false,
        ];
    }

}