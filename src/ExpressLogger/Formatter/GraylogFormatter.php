<?php

namespace ExpressLogger\Formatter;

use ExpressLogger\API\FormatterInterface;
use ExpressLogger\PsrSysLevel;

class GraylogFormatter implements FormatterInterface
{
    use ToStringTrait;

    protected array $exclude = [
        'host' => '',
        'message' => '',
        'level' => '',
        'datetime' => '',
    ];

    protected $host = '';

    /**
     * JsonFormatter constructor.
     * @param int|null $jsonFlags json_encode flags param
     */
    public function __construct(?int $jsonFlags = null)
    {
        if (null !== $jsonFlags) {
            $this->setJsonFlags($jsonFlags);
        }
        $this->host = gethostname();
    }

    public function format(array $data): string
    {
        $result = [
            'version' => '1.1',
            'host' => $data['host'] ?? $this->host,
            'short_message' => $data['short_message'] ?? $this->removeEol($data['message']),
            'full_message' => $data['full_message'] ?? $data['message'],
            'level' => PsrSysLevel::getSysLevel($data['level']),
            'timestamp' => $data['datetime']->format("U.u"),
        ];

        $additional = array_filter($data, fn($key) => !isset($this->exclude[$key]), ARRAY_FILTER_USE_KEY);
        foreach ($additional as $key => $value) {
            if ($key === 'id') {
                $key = 'x_id';
            }
            $result['_' . $key] = is_bool($value) ? ( $value ? 'true' : 'false') : $this->toString($value);
        }

        return $this->toString($result);
    }
}
