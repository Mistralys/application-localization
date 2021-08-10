<?php

    use function AppLocalize\t;

    $textA = t('Global context');
    
    $textB = t("With double quotes");
    
    function bothWorlds() : string
    {
        return t('Within function');
    }
    
    $multiline = t(
        'Multiline '.
        'text '.
        'over '.
        'several '.
        'concatenated ' .
        'lines'
    );
    
    $someVariable = '';
    
    $withVar = t('With variable '.$someVariable);
    
    $withClosure = t(function() {
        return t('Within a closure');
    });
    
    class PHP_Translations_Testfile
    {
        private function translateMe() : string
        {
            return t('Within class method.');
        }
    }
    
    $withPlaceholders = t('This is %1$sbold%2$s text.', '<b>', '</b>');
    
    $inception = t('A %1$s text within a translated text.', t('translated text'));
