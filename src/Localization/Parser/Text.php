<?php

declare(strict_types=1);

namespace AppLocalize\Localization\Parser;

/**
 * @phpstan-type SerializedText array{text:string, line:int, explanation:string}
 */
class Text
{
    public const SERIALIZED_TEXT = 'text';
    public const SERIALIZED_LINE = 'line';
    public const SERIALIZED_EXPLANATION = 'explanation';

    private string $text;
    private int $line;
    private string $explanation;
    private string $hash;

    public function __construct(string $text, int $line, string $explanation='')
    {
        $this->text = $text;
        $this->line = $line;
        $this->explanation = $explanation;
        $this->hash = md5($this->text);
    }

    /**
     * @return string
     */
    public function getExplanation() : string
    {
        return $this->explanation;
    }

    public function isEmpty() : bool
    {
        return $this->text === '';
    }

    /**
     * @return int
     */
    public function getLine() : int
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getHash() : string
    {
        return $this->hash;
    }

    /**
     * @return SerializedText
     */
    public function toArray() : array
    {
        return array(
            self::SERIALIZED_TEXT => $this->getText(),
            self::SERIALIZED_LINE => $this->getLine(),
            self::SERIALIZED_EXPLANATION => $this->getExplanation()
        );
    }

    /**
     * @param SerializedText $array
     * @return Text
     */
    public static function fromArray(array $array) : Text
    {
        return new Text(
            $array[self::SERIALIZED_TEXT],
            $array[self::SERIALIZED_LINE],
            $array[self::SERIALIZED_EXPLANATION]
        );
    }
}
