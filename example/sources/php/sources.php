<?php

    declare(strict_types=1);

    use function AppLocalize\t;
    use function AppLocalize\pt;

    function textsInFunction() : string
    {
        return t('This text is in a function.');
    }
    
    $textA = t('This text is in the global scope.');
    
    $textB = t('This text is used serverside and on the client.');
    
    class Texts
    {
        public function display() : void
        {
            pt('This text is printed from a class.');
        }
    }
    
    function textsDuplicates() : string
    {
        return t('This text is in a function.');
    }
