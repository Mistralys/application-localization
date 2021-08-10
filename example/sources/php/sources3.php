<?php

    declare(strict_types=1);

    use function AppLocalize\t;

    $text = t('This text is in the global scope.');
    
    $text2 = t(
        'It is possible to write long texts ' .
        'using text concatenation in the source ' .
        'code to keep it readable.'
    );