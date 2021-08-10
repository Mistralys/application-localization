<?php

    declare(strict_types=1);

    use function AppLocalize\tex;

    $textA = tex(
        'Text with translation context',
        'This context description is shown in the translation UI '.
        'to help translators understand the context in which the '.
        'text is used. The description can be as long as needed, '.
        'and can even <strong>contain HTML for formatting</strong>.'
    );
