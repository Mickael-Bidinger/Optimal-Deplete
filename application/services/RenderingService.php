<?php

namespace App\services;


use App\repository\AffixRepository;
use App\repository\AffixSetRepository;
use App\repository\DungeonRepository;
use App\repository\FactionRepository;
use App\repository\SpecRepository;
use MB\Displayer;

class RenderingService
{
    private const COLOR_1_CHEST = '#3366cc';
    private const COLOR_2_CHEST = '#dc3912';
    private const COLOR_3_CHEST = '#ff9900';
    private const BAR_HEIGHT = 16;
    private const BAR_GAP = 29;
    private $affixRepository;
    private $affixSetRepository;
    private $height;
    private $displayer;
    private $dungeonRepository;
    private $factionRepository;
    private $specRepository;

    public function __construct()
    {
        $this->affixRepository = new AffixRepository();
        $this->affixSetRepository = new AffixSetRepository();
        $this->dungeonRepository = new DungeonRepository();
        $this->displayer = new Displayer();
        $this->factionRepository = new FactionRepository();
        $this->specRepository = new SpecRepository();
    }

    public function run(array $counts): array
    {
        if (empty($counts['total'])) {
            return [
                'sorting' => $this->getSorting($counts),
                'h3' => 'Timed keys',
                'graphic' => '<p>No result for your query, try widening your search.</p>',
                'details' => '',
            ];
        }
        $this->height = (self::BAR_GAP - self::BAR_HEIGHT) / 2;

        $totalRuns = $counts['total'][0]['total'];

        $detailsTable = $this->getDetailsTbody($counts['total'][0], $totalRuns);
        $groupLegend = $this->getGroupLegend($counts['total'][0]);
        $group = $this->getGroup($counts['total'][0]);

        $details = "<div class='hover-container'><h4>$groupLegend</h4><table>$detailsTable</table></div>";
        $graphic['svg'] = "<g>$group</g>";
        $graphic['ul'] = "<li>$groupLegend</li>";

        foreach ($counts['counts'] as $count) {
            $detailsTable = $this->getDetailsTbody($count, $totalRuns);
            $groupLegend = $this->getGroupLegend($count, $counts['sorting']);
            $group = $this->getGroup($count);

            $details .= "<div class='hover-container'><h4>$groupLegend</h4><table>$detailsTable</table></div>";
            $graphic['svg'] .= "<g>$group</g>";
            $graphic['ul'] .= "<li>$groupLegend</li>";
        }

        $this->height += self::BAR_GAP / 2;

        $graphic = "
            <ul>
                {$graphic['ul']}
            </ul>
            <svg width='1000' height='$this->height' preserveAspectRatio='none'>
                <rect x='20%' y='0' width='1' height='100%' fill='white' opacity='100%'/>
                <rect x='40%' y='0' width='1' height='100%' fill='white' opacity='100%'/>
                <rect x='60%' y='0' width='1' height='100%' fill='white' opacity='100%'/>
                <rect x='80%' y='0' width='1' height='100%' fill='white' opacity='100%'/>
                {$graphic['svg']}
            </svg>
        ";

        return [
            'graphic' => $graphic,
            'sorting' => $this->getSorting($counts),
            'h3' => $this->getH3($counts),
            'details' => $details,
        ];
    }

    private function getDetailsTbody(array $count, int $totalRuns): string
    {
        $chest0 = $count['0'];
        $chest1 = $count['1'];
        $chest2 = $count['2'];
        $chest3 = $count['3'];
        $total = $count['total'];
        $timed = $total - $chest0;

        $chest0Percent = \number_format($chest0 / $total * 100, 0);
        $chest1Percent = \number_format($chest1 / $total * 100, 0);
        $chest2Percent = \number_format($chest2 / $total * 100, 0);
        $chest3Percent = \number_format($chest3 / $total * 100, 0);
        $timedPercent = \number_format($timed / $total * 100, 0);
        $totalPercent = \number_format($total / $totalRuns * 100, 0);

        $chest0 = \number_format($chest0);
        $chest1 = \number_format($chest1);
        $chest2 = \number_format($chest2);
        $chest3 = \number_format($chest3);
        $total = \number_format($total);
        $timed = \number_format($timed);

        $tbody = "<tr><th>Total:</th><td>$totalPercent%</td><td>$total&nbsp;runs</td></tr>";
        $tbody .= "<tr><th>Timed:</th><td>$timedPercent%</td><td>$timed&nbsp;runs</td></tr>";
        $tbody .= "<tr><th>1&nbsp;chest:</th><td>$chest1Percent%</td><td>$chest1&nbsp;runs</td></tr>";
        $tbody .= "<tr><th>2&nbsp;chest:</th><td>$chest2Percent%</td><td>$chest2&nbsp;runs</td></tr>";
        $tbody .= "<tr><th>3&nbsp;chest:</th><td>$chest3Percent%</td><td>$chest3&nbsp;runs</td></tr>";
        $tbody .= "<tr><th>Depleted:</th><td>$chest0Percent%</td><td>$chest0&nbsp;runs</td></tr>";

        return "<tbody>$tbody</tbody>";
    }

    private function getGroup(array $count): string
    {
        $chest1Percent = $count['1'] / $count['total'] * 100;
        $chest2Percent = $count['2'] / $count['total'] * 100;
        $chest3Percent = $count['3'] / $count['total'] * 100;
        $group = '';
        $x1 = 0;
        $x2 = $chest1Percent;
        $x3 = $chest1Percent + $chest2Percent;

        $group .= $this->getRectangle(
            "$x1%",
            $this->height,
            "$chest1Percent%",
            self::BAR_HEIGHT,
            self::COLOR_1_CHEST
        );
        $group .= $this->getRectangle(
            "$x2%",
            $this->height,
            "$chest2Percent%",
            self::BAR_HEIGHT,
            self::COLOR_2_CHEST
        );
        $group .= $this->getRectangle(
            "$x3%",
            $this->height,
            "$chest3Percent%",
            self::BAR_HEIGHT,
            self::COLOR_3_CHEST
        );

        $this->height += self::BAR_GAP;

        return $group;
    }

    private function getGroupLegend(array $count, ?string $sorting = null): string
    {
        $groupLegend = '';

        switch ($sorting) {

            case 'specialization':

                $spec = $this->specRepository->find($count['_id']);
                $groupLegend = \sprintf(
                    '<span>%2$s</span><img src="%1$s" alt="%2$s">',
                    RELATIVE_ROOT_PATH . '/' . $this->displayer->string($spec->getImage()),
                    $this->displayer->string($spec, '-', ' ')
                );
                break;


            case 'dungeon':

                $dungeon = $this->dungeonRepository->find($count['_id']);
                $groupLegend = \sprintf(
                    '<span>%1$s</span>',
                    $this->displayer->string($dungeon, '.*-', '')
                );
                break;


            case 'level':

                $groupLegend = \sprintf(
                    '<span>+%1$d</span>',
                    $this->displayer->string($count['_id'])
                );
                break;


            case 'faction':

                $faction = $this->factionRepository->find($count['_id']);
                $groupLegend = \sprintf(
                    '<span>%1$s</span>',
                    $this->displayer->string($faction, '-', ' ')
                );
                break;


            case 'affix':

                $affixSet = $this->affixSetRepository->find($count['_id']);
                foreach ($affixSet->getAffixes() as $affix) {
                    $groupLegend .= \sprintf(
                        '<img src="%1$s" alt="%2$s" title="%2$s">',
                        RELATIVE_ROOT_PATH . '/' . $this->displayer->string($affix->getImage()),
                        $this->displayer->string($affix, '-', ' ')
                    );
                }
                break;


            case null:

                $groupLegend = '<span>Average</span>';
        }


        return $groupLegend;

    }

    private function getH3(array $counts): string
    {
        return $counts['sorting'] === 'none' ? 'Timed keys - average only' : 'Timed keys - by ' . $counts['sorting'];
    }

    private function getRectangle(string $x, string $y, string $width, string $height, string $color): string
    {
        return "<rect x='$x' y='$y' width='$width' height='$height' fill='$color' opacity='100%'/>";
    }

    private function getSorting(array $counts): string
    {
        $sortings = '';

        foreach ($counts['sortings'] as $availableSorting) {
            $selected = $availableSorting === $counts['sorting'] ? 'class="selected"' : '';
            $title = $availableSorting === 'none' ? 'Show average only' : "Sort by $availableSorting";
            $textContent = \ucfirst($availableSorting);
            $sortings .= '
                <li>
                    <a href=\'' . RELATIVE_ROOT_PATH . "sorting-$availableSorting' 
                       data-type='sorting'
                       data-sorting='$availableSorting'
                       aria-label='$title'
                       title='$title'
                       $selected
                    >
                         $textContent
                    </a>
                </li>";
        }

        return "<ul>$sortings</ul>";
    }

}