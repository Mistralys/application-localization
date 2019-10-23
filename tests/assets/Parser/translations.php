<?php

    $text = t('Global context');
    
    $text = t("With double quotes");
    
    function bothWorlds()
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
        private function translateMe()
        {
            return t('Within class method.');
        }
    }
    
    $withPlaceholders = t('This is %1$sbold%2$s text.', '<b>', '</b>');
    
    $inception = t('A %1$s text within a translated text.', t('translated text'));