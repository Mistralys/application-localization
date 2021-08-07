<?php

declare(strict_types=1);

namespace AppLocalize\Editor;

class OutputBuffering
{
    const ERROR_RENDERING_FAILED = 91301;

    public static function start() : void
    {
        ob_start();
    }

    /**
     * @return string
     * @throws EditorException
     */
    public static function getClean() : string
    {
        $html = ob_get_clean();

        if($html !== false)
        {
            return $html;
        }

        throw new EditorException(
            'Rendering failed',
            'Output buffering returned false.',
            self::ERROR_RENDERING_FAILED
        );
    }
}
