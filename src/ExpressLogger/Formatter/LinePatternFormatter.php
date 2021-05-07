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
 * Class LinePatternFormatter
 * @package Logger\Formatters
 */
class LinePatternFormatter implements FormatterInterface
{
    use ToStringTrait;

    protected string $logFormat = "%datetime% %level%[%level_code%] %request_id%: %message%\n" ;
    protected array $delimiter = ['%','%'];
    protected array $templateAside = [];
    protected array $templateKeys = [];

    /**
     * LinePatternFormatter constructor.
     * @param string|null $dateFormat
     * @param string|null $logFormat
     * @param null|string|array $delimiter
     */
    public function __construct(?string $dateFormat = null, ?string $logFormat = null, $delimiter = '%')
    {
        if ($dateFormat) {
            $this->dateFormat = $dateFormat;
        }
        if ($logFormat) {
            $this->setLogFormat($logFormat);
        }
        if ($delimiter) {
            $this->setDelimiter(is_array($delimiter)
                ? $delimiter
                : [ $delimiter, $delimiter]);
        }

        $this->prepareTemplate();
    }

    /**
     * Parses log template and extracts keys.
     */
    protected function prepareTemplate(): void
    {
        $regex = "/{$this->delimiter[0]}[^{$this->delimiter[1]}]+{$this->delimiter[1]}/";
        $this->templateAside = preg_split($regex, $this->logFormat);
        $regex = "/{$this->delimiter[0]}([^{$this->delimiter[1]}]+){$this->delimiter[1]}/";
        preg_match_all($regex, $this->logFormat, $matches);
        $this->templateKeys = $matches[1];
        unset($matches);
    }
    /**
     * @param array $data
     * @return string
     */
    public function format(array $data): string
    {
        $limit = count($this->templateKeys);
        $result = '';
        for ($i = 0; $i < $limit; $i++) {
            $result .= $this->templateAside[$i] . $this->toString($data[$this->templateKeys[$i]] ?? '');
        }
        $result .= $this->templateAside[$i];
        return $result;
    }

    /**
     * @param string $logFormat
     */
    public function setLogFormat(string $logFormat): void
    {
        $this->logFormat = $logFormat;
    }

    /**
     * @param array $delimiter
     */
    public function setDelimiter(array $delimiter): void
    {
        $this->delimiter = $delimiter;
    }
}
