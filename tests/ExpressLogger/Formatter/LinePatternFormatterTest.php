<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\Formatter;

use ExpressLogger\Formatter\LinePatternFormatter;
use PHPUnit\Framework\TestCase;

class LinePatternFormatterTest extends TestCase
{
    /**
     * @testdox Should log only defined in pattern fields
     */
    public function testLinePatternFormatter()
    {
        $formatter = new LinePatternFormatter('Y-m-d','[{{datetime}}]: {{message}}', ['{{', '}}']);

        $log = [
            'message' => 'Log message',
            'datetime' => new \DateTime('2021-05-01'),
        ];
        $message = $formatter->format($log);
        $this->assertStringContainsString('[2021-05-01]: Log message', $message);
    }
}
