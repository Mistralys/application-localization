<?php

    declare(strict_types=1);

    use function AppLocalize\t;

    $textA = t('Found %1$s items in %2$s pages.', 50, 6);
    
    $textB = t('There are %1$s%2$s%3$s fish in the sea.', '<b>', 'many', '</b>');
    
    $textC = t('Zero-padded placeholders like %03d are also recognized.', 5);
    
    $textD = t('You may use %1$s as placeholders.', t('translated texts'));
