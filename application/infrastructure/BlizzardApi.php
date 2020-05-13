<?php


namespace App\infrastructure;


class BlizzardApi
{
    private $curl;
    private $urls;

    public function __construct()
    {
        $this->curl = \curl_init();
        $this->urls = [
            'eu' => 'https://eu.api.blizzard.com/data/wow/%s?namespace=%s-eu&locale=en_US',
            'us' => 'https://us.api.blizzard.com/data/wow/%s?namespace=%s-us&locale=en_US',
            'kr' => 'https://kr.api.blizzard.com/data/wow/%s?namespace=%s-kr&locale=en_US',
            'tw' => 'https://tw.api.blizzard.com/data/wow/%s?namespace=%s-tw&locale=en_US',
        ];

        $authToken = $this->getToken();
        \curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($this->curl, CURLOPT_HTTPHEADER, ["Authorization: Bearer $authToken"]);
        \curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
        \curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);
    }

    private function getToken(): string
    {
        $infrastructure = \file_get_contents(ROOT_PATH . '/config/infrastructure.json');
        $infrastructure = \json_decode($infrastructure, true);
        $infrastructure = $infrastructure['blizzardApi'];

        $authorization = "{$infrastructure['Client-ID']}:{$infrastructure['Client-secret']}";
        $authorization = 'Basic ' . \base64_encode($authorization);

        $context = \stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Authorization: $authorization",
                'content' => 'grant_type=client_credentials',
            ],
        ]);

        $token = \file_get_contents('https://eu.battle.net/oauth/token', false, $context);
        $token = \json_decode($token, true);

        return $token['access_token'];
    }

    private function getByUrl(string $url)
    {
        \curl_setopt($this->curl, CURLOPT_URL, $url);
        $data = \curl_exec($this->curl);

        if (\curl_errno($this->curl)) {
            return null;
        }

        return \json_decode($data, true);
    }

    public function findAffixInfo(int $affixId): array
    {
        $url = sprintf($this->urls['eu'], "keystone-affix/$affixId", 'static');
        $affixName = $this->getByUrl($url)['name'];

        $url = sprintf($this->urls['eu'], "media/keystone-affix/$affixId", 'static');
        $assets = $this->getByUrl($url)['assets'];

        foreach ($assets as $asset) {
            if ($asset['key'] === 'icon') {
                $affixImage = $asset['value'];
            }
        }

        return [
            'id' => $affixId,
            'name' => $affixName,
            'image' => $affixImage ?? null
        ];
    }

    public function findClassImage(int $classId): ?string
    {
        $url = sprintf($this->urls['eu'], "media/playable-class/$classId", 'static');
        $assets = $this->getByUrl($url)['assets'];

        foreach ($assets as $asset) {
            if ($asset['key'] === 'icon') {
                $image = $asset['value'];
            }
        }

        return $image ?? null;
    }

    public function findCurrentSeason(): ?int
    {
        $url = sprintf($this->urls['eu'], "mythic-keystone/season/index", 'dynamic');

        return $this->getByUrl($url)['current_season']['id'] ?? null;
    }

    public function findDungeonInfo(int $dungeonId): array
    {
        $url = sprintf($this->urls['eu'], "mythic-keystone/dungeon/$dungeonId", 'dynamic');
        $dungeon = $this->getByUrl($url);

        $url = sprintf($this->urls['eu'], "media/journal-instance/{$dungeon['dungeon']['id']}", 'static');
        $assets = $this->getByUrl($url)['assets'];

        foreach ($assets as $asset) {
            if ($asset['key'] === 'tile') {
                $image = $asset['value'];
            }
        }

        $dungeonChests = [
            $dungeon['keystone_upgrades'][0]['qualifying_duration'],
            $dungeon['keystone_upgrades'][1]['qualifying_duration'],
            $dungeon['keystone_upgrades'][2]['qualifying_duration']
        ];

        return [
            'image' => $image ?? null,
            'chests' => $dungeonChests,
        ];
    }

    public function findSpec(int $specId): array
    {
        $url = sprintf($this->urls['eu'], "playable-specialization/$specId", 'static');
        return $this->getByUrl($url);
    }

    public function findSpecImage(int $specId): ?string
    {
        $url = sprintf($this->urls['eu'], "media/playable-specialization/$specId", 'static');
        $assets = $this->getByUrl($url)['assets'];

        foreach ($assets as $asset) {
            if ($asset['key'] === 'icon') {
                $image = $asset['value'];
            }
        }
        return $image ?? null;
    }

    public function listAffixes(): array
    {
        $url = sprintf($this->urls['eu'], "keystone-affix/index", 'static');
        return $this->getByUrl($url)['affixes'];
    }

    public function listDungeons(): array
    {
        $url = sprintf($this->urls['eu'], "mythic-keystone/dungeon/index", 'dynamic');
        return $this->getByUrl($url)['dungeons'];
    }

    public function listLeaderboards(string $region, int $realm, int $dungeon, int $period): ?array
    {
        $url = sprintf(
            $this->urls[$region],
            "connected-realm/$realm/mythic-leaderboard/$dungeon/period/$period",
            'dynamic'
        );

        return $this->getByUrl($url);
    }

    public function listPeriodsBySeason(int $season): array
    {
        $url = sprintf($this->urls['eu'], "mythic-keystone/season/$season", 'dynamic');
        return $this->getByUrl($url)['periods'];
    }

    public function listRealmsByRegion(string $region): array
    {
        $url = sprintf($this->urls[$region], "connected-realm/index", 'dynamic');
        $response = $this->getByUrl($url)['connected_realms'];

        $realms = [];
        foreach ($response as $realm) {
            $realm = \explode('/', $realm['href']);
            $realm = \explode('?', \array_pop($realm));
            $realms[] = (int)\array_shift($realm);
        }

        return $realms;
    }

    public function listSpecs(): array
    {
        $url = sprintf($this->urls['eu'], "playable-specialization/index", 'static');

        return $this->getByUrl($url)['character_specializations'];
    }

}
