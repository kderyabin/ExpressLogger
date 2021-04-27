<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\Filter;

use ExpressLogger\API\FilterCollectionInterface;
use ExpressLogger\Filter\FilterCollectionTrait;
use PHPUnit\Framework\TestCase;

class FilterCollectionTraitTest extends TestCase
{
    const LOG =   [ 'level' => 'info', 'message' => null, 'user' => 'john@doe', 'request' => '1'];
    /**
     * @testdox Should apply filter
     * @test
     */
    public function filter()
    {
        $filter = new class() implements FilterCollectionInterface {
            use FilterCollectionTrait;
        };
        // arrow function
        $filter->addFilter(fn($data) => array_filter($data, fn($value) => !empty($value)));
        // classic function
        $filter->addFilter(function($data) {
            $data['user'] = 'xxx';
           return $data;
        });
        // Invokable object
        $requestFilter = new class() {
            public function __invoke($data)
            {
                $data['request'] = '999';
                return $data;
            }
        };
        $filter->addFilter($requestFilter);
        $result = $filter->applyFilters(self::LOG);

        $this->assertArrayNotHasKey('message', $result);
        $this->assertEquals('xxx', $result['user']);
        $this->assertEquals('999', $result['request']);
    }
    /**
     * @testdox Should stop applying filters if one of them invalidates the log data.
     * @test
     */
    public function filterBreak()
    {
        $filter = new class() implements FilterCollectionInterface {
            use FilterCollectionTrait;
        };
        $filter->addFilter(fn($data) => array_filter($data, fn($value) => !empty($value)));
        $filter->addFilter(fn($data) => false );

        $result = $filter->applyFilters(self::LOG);

        $this->assertFalse($result);
    }
}
