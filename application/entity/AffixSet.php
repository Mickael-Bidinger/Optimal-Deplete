<?php

namespace App\entity;


class AffixSet
{
    private $id;
    /** @var Affix[] */
    private $affixes;

    public function __construct(int $id, array $affixes)
    {
        $this->id = $id;
        $this->affixes = [
            new Affix(
                $affixes['affix1_id'],
                $affixes['affix1_name'],
                $affixes['affix1_image'],
                $affixes['affix1_starting_level']
            ),
            new Affix(
                $affixes['affix2_id'],
                $affixes['affix2_name'],
                $affixes['affix2_image'],
                $affixes['affix2_starting_level']
            ),
            new Affix(
                $affixes['affix3_id'],
                $affixes['affix3_name'],
                $affixes['affix3_image'],
                $affixes['affix3_starting_level']
            ),
            new Affix(
                $affixes['affix4_id'],
                $affixes['affix4_name'],
                $affixes['affix4_image'],
                $affixes['affix4_starting_level']
            ),
        ];
    }

    public function __toString(): string
    {
        return "{$this->affixes[0]->getId()}.{$this->affixes[1]->getId()}.{$this->affixes[2]->getId()}.{$this->affixes[3]->getId()}";
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Affix[]
     */
    public function getAffixes(): array
    {
        return $this->affixes;
    }


}