<?php
/*
 * Copyright (c) 2021 Konstantin Deryabin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExpressLogger\Tests\Formatter;

use DateTime;
use ExpressLogger\Formatter\ToStringTrait;
use PHPUnit\Framework\TestCase;

class ToStringTraitTest extends TestCase
{
    protected $instance;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->instance = new class () {
            use ToStringTrait;
        };
        parent::__construct($name, $data, $dataName);
    }


    public function toStringDataProvider(): array
    {
        $resource = fopen('php://memory', 'a');
        $resourceClosed = fopen('php://memory', 'a');
        fclose($resourceClosed);

        $stringable = new class () {
            public function __toString()
            {
                return 'Stringable';
            }
        };

        return [
            ["string", "string"],
            [1, '1'],
            [false, 'false'],
            [true, 'true'],
            [$resource, (string)$resource],
            [$resourceClosed, ((string)$resourceClosed . ' (closed)')],
            [[1, 2, 3], '[1,2,3]'],
            [new DateTime('2021-01-12 08:30:15'), '2021-01-12'],
            [$stringable, 'Stringable']
        ];
    }

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString($value, $expected)
    {
        $this->instance->setDateFormat('Y-m-d');
        $this->assertSame($expected, $this->instance->toString($value));
    }

    public function testRemoveEOL()
    {
        $input = "1 Line\n2 Line\r\n";
        $this->assertEquals('1 Line2 Line', $this->instance->removeEol($input));
    }
}
