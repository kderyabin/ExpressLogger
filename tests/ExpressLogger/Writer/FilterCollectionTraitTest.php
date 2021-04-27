<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\Writer;

use ExpressLogger\API\FilterCollectionInterface;
use ExpressLogger\Writer\FilterCollectionTrait;
use PHPUnit\Framework\TestCase;

class FilterCollectionTraitTest extends TestCase
{
    /**
     * @testdox Should apply filter
     * @test
     */
    public function filter()
    {
        $filter = new class() implements FilterCollectionInterface {
            use FilterCollectionTrait;
        };
        $data = [ 'level' => 'info', 'message' => null, 'user' => 'john@doe', 'request' => '1'];
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
        $result = $filter->applyFilters($data);

        $this->assertArrayNotHasKey('message', $result);
        $this->assertEquals('xxx', $result['user']);
        $this->assertEquals('999', $result['request']);
    }
}
