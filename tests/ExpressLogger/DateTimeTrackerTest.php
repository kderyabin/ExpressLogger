<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests;

use ExpressLogger\DateTimeTracker;
use PHPUnit\Framework\TestCase;

class DateTimeTrackerTest extends TestCase
{

    public function testGetNow()
    {
        $dt = new DateTimeTracker();
        sleep(1);
        $now = $dt->getNow();
        $diff = $dt->diff($now);

        $this->assertFalse($dt === $now);
        $this->assertEquals(1, $diff->s);
    }
}
