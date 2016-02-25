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


    public function generateCategoryTitle()
    {
        return $this->generateAdjectivesNounPair();
    }

    public function generateProductTitle()
    {
        return $this->generateAdjectivesNounPair();
    }

    protected function generateAdjectivesNounPair()
    {
        $adjectives = '';
        for ($cnt = 0; $cnt < 2; $cnt++) {
            $adjectives .= $this->adjectives[rand(0, (count($this->adjectives) - 1))] . ' ';
        }

        $noun = $this->nouns[rand(0, (count($this->nouns) - 1))];

        return $adjectives . $noun;
    }

}