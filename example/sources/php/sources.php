<?php

    function textsInFunction()
    {
        t('This text is in a function.');
    }
    
    t('This text is in the global scope.');
    
    t('This text is used serverside and on the client.');
    
    class Texts
    {
        public function display()
        {
            pt('This text is printed from a class.');
        }
    }
    
    function textsDuplicates()
    {
        t('This text is in a function.');
    }