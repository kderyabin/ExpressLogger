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
 * @package ExpressLogger\Formatter
 */
class KeyFormatter implements FormatterInterface
{
    use ToStringTrait;

    /**
     * End line to append to generated json.
     * @var string
     */
    protected string $eol = '';
    /**
     * KeyFormatter constructor.
     * @param string|null $dateFormat
     * @param string $eol Log message end line.
     */
    public function __construct(?string $dateFormat = null, string $eol = "\n")
    {
        if ($dateFormat) {
            $this->setDateFormat($dateFormat);
        }
        $this->eol = $eol;
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
        $result .= $this->eol;
        return $result;
    }
}
