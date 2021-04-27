<?php

/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace ExpressLogger\Formatter;

use ExpressLogger\API\FormatterInterface;

/**
 * Class KeyFormatter
 * @package Logger\Formatters
 */
class KeyFormatter implements FormatterInterface
{
    use ToStringTrait;

    protected string $endLine;
    /**
     * KeyFormatter constructor.
     * @param string|null $dateFormat
     * @param string $endLine Log message end line.
     */
    public function __construct(?string $dateFormat = null, string $endLine = "\n")
    {
        if ($dateFormat) {
            $this->dateFormat = $dateFormat;
        }
        $this->endLine = $endLine;
    }

    /**
     * @param array $data
     * @return string
     */
    public function format(array $data): string
    {
        $result = '';
        foreach ($data as $key => $value) {
            $result .= $key . ': ' . $this->toString($value) . ' ';
        }
        $result .= $this->endLine;
        return $result;
    }
}
