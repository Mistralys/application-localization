<?php

declare(strict_types=1);

namespace AppLocalize\Parser;

class Text
{
    const SERIALIZED_TEXT = 'text';
    const SERIALIZED_LINE = 'line';
    const SERIALIZED_EXPLANATION = 'explanation';
    /**
     * @var string
     */
    private $text;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $explanation;

    /**
     * @var string
     */
    private $hash;

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
     * @return array<string,mixed>
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
     * @param array<string,mixed> $array
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
