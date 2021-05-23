<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\Filter;

use ExpressLogger\Filter\EmptyFilter;
use PHPUnit\Framework\TestCase;

class EmptyFilterTest extends TestCase
{

    public function testFilter()
    {
        $data = [ 'message' => 'hi', 'user' => null, 'login' => ''];
        $filter = new EmptyFilter();

        $result = $filter->filter($data);

        $this->assertSame(1, count($result));
        $this->assertArrayHasKey('message', $data);
    }
}
