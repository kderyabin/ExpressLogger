<?php

/*
 * Copyright (c) 2021. Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ExpressLogger;

use DateTimeZone;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimeTracker extends DateTimeImmutable
{
    /**
     * @var int|float
     */
    protected $timer;
    protected \DateInterval $dateInterval;

    /**
     * DateTimeTracker constructor.
     * @param string $datetime
     */
    public function __construct($datetime = 'now')
    {
        try {
            parent::__construct($datetime, new DateTimeZone(date_default_timezone_get()));
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
        $this->timer = hrtime(true);
        $this->dateInterval = new DateInterval('PT0S');
    }

    /**
     * @return DateTimeInterface
     */
    public function getNow(): DateTimeInterface
    {
        // convert nanoseconds to microseconds for setting in the DateInterval
        $this->dateInterval->f = (hrtime(true) - $this->timer) / 1e+9;
        return $this->add($this->dateInterval);
    }
}
