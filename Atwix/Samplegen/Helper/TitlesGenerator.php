<?php

namespace Atwix\Samplegen\Helper;

class TitlesGenerator
{
    protected $adjectives = ['amazing', 'ambitious', 'ample', 'amused', 'amusing', 'anchored', 'ancient', 'angelic',
                             'angry', 'anguished', 'animated', 'annual', 'another', 'antique'];

    protected $nouns = ['aftermath', 'afternoon', 'afterthought', 'apparel', 'appliance', 'beginner', 'believe', 'bomb',
                        'border', 'boundary', 'breakfast', 'cabbage', 'cable', 'calculator', 'calendar', 'caption',
                        'carpenter', 'channel', 'circle', 'creator', 'creature', 'education', 'faucet', 'feather',
                        'friction', 'fruit', 'fuel', 'galley', 'guide', 'guitar', 'health', 'heart', 'idea', 'kitten',
                        'laborer', 'language', 'lawyer', 'linen', 'locket', 'lumber', 'magic', 'minister', 'mitten',
                        'money'];

    protected $firstNames = ['Mary', 'Anna', 'Ruth', 'Margaret', 'Elizabeth', 'Helen', 'Florence', 'Ethel', 'Emma',
                             'Bertha', 'Clara', 'Annie', 'Mabel', 'Nellie', 'Nellie', 'Louise', 'Julia', 'Cora', 'Mae',
                             'Josephine', 'Ruby', 'Lydia', 'Lottie', 'Michel', 'Miquel', 'Reginald', 'Jude',
                             'Florentino', 'Quinn', 'Marshall', 'Deon', 'Alfonso', 'Jordon', 'Tad', 'Matthew'];


    protected $lastNames = ['Lal', 'Maass', 'Wilt', 'Vegas', 'Strum', 'Maxey', 'Enz', 'Mcavoy', 'Galvez', 'Chi',
                            'Mervis', 'Lapp', 'Seibold', 'Philson', 'Trail', 'Granata', 'Hazelwood', 'Toft', 'Sauve',
                            'Valverde', 'Hiott', 'Sainz', 'Deno', 'Deering', 'Barbosa', 'Barbosa', 'Vargo', 'Gullion',
                            'Plant', 'Speas', 'Carrithers'];

    public function generateCategoryTitle()
    {
        return $this->generateAdjectivesNounPair();
    }

    public function generateProductTitle()
    {
        return $this->generateAdjectivesNounPair();
    }

    public function generateCustomerName()
    {
        return $this->generateTitlePair($this->firstNames, $this->lastNames);
    }

    protected function generateAdjectivesNounPair()
    {
        return $this->generateTitlePair($this->adjectives, $this->nouns);
    }

    protected function generateTitlePair($part1Set, $part2Set)
    {
        $part1 = '';
        for ($cnt = 0; $cnt < 2; $cnt++) {
            $part1 .= $part1Set[rand(0, (count($part1Set) - 1))] . ' ';
        }

        $part2 = $part2Set[rand(0, (count($part2Set) - 1))];

        return $part1 . $part2;
    }
}