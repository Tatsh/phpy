<?php
namespace PythonBuiltins;

use PHPUnit\Framework\TestCase;

use PythonBuiltins as f;

class PythonBuiltinsTest extends TestCase
{
    public function testAll()
    {
        $this->assertTrue(f\all([true]));
        $this->assertTrue(f\all([1, true, 'non empty string', '0.0']));
        $this->assertFalse(f\all([0, true, 'non empty string', '0.0']));
        $this->assertFalse(f\all([1, false, 'non empty string', '0.0']));
        $this->assertFalse(f\all([1, true, '', '0.0']));
        $this->assertFalse(f\all([1, true, 'non-empty string', 0.0]));
        $this->assertTrue(f\all([new \stdClass()]));
    }

    public function testAny()
    {
        $this->assertTrue(f\any([true]));
        $this->assertFalse(f\any([false, 0]));
        $this->assertTrue(f\any([1, true, 'non empty string', '0.0']));
        $this->assertTrue(f\any([0, true, 'non empty string', '0.0']));
        $this->assertTrue(f\any([1, false, 'non empty string', '0.0']));
        $this->assertTrue(f\any([1, true, '', '0.0']));
        $this->assertTrue(f\any([1, true, 'non-empty string', 0.0]));
        $this->assertTrue(f\any([new \stdClass(), false]));
    }

    /**
     * @covers ::\PythonBuiltins\ascii
     * @covers ::\PythonBuiltins\repr
     */
    public function testAscii()
    {
        $this->assertSame('anonymous class', f\ascii(new class {
            public function __toString() {
                return 'anonymous class';
            }
        }));
        $this->assertSame('111', f\ascii(111));
        $this->assertSame('1', f\ascii(0x1));
        $this->assertSame('1', f\ascii(01));
        $this->assertSame('7', f\ascii(0b111));
    }

    public function testBin()
    {
        $this->assertSame('111', f\bin(7));
    }

    public function testBool()
    {
        $this->assertFalse(f\bool(false));
        $this->assertTrue(f\bool(true));
        $this->assertFalse(f\bool(0));
        $this->assertTrue(f\bool(1));
        $this->assertFalse(f\bool(0.0));
        $this->assertTrue(f\bool('0.0'));
        $this->assertFalse(f\bool(''));
        $this->assertTrue(f\bool('a value'));
    }

    public function testBytearray()
    {
        $x = bytearray('abcdef', 'UTF-8');

        $this->assertSame('b', $x[1]);
        $x[1] = 'z';
        $this->assertSame('z', $x[1]);
        $this->assertSame(6, count($x));
        unset($x[1]);
        $this->assertSame('c', $x[1]);
        $this->assertSame('acdef', (string) $x);
        $this->assertSame('6163646566', $x->hex());

        $this->assertFalse(isset($x[99]));
    }

    /**
     * @expectedException PythonBuiltins\TypeError
     */
    public function testBytearray2()
    {
        bytearray('abcdef');
    }

    public function testBytearrayIterator()
    {
        $x = bytearray(new \ArrayIterator(['a', 'b']));

        $this->assertSame('b', $x[1]);
        $x[1] = 'z';
        $this->assertSame('z', $x[1]);
    }

    /**
     * @expectedException PythonBuiltins\ValueError
     */
    public function testBytearrayNoMultibyte()
    {
        bytearray('abcdef', 'UTF-8')[1] = '且';
    }

    /**
     * @expectedException PythonBuiltins\ValueError
     */
    public function testBytearrayNoMultibyte2()
    {
        bytearray('abcdef', 'UTF-8')[1] = 'ab';
    }

    /**
     * @expectedException PythonBuiltins\ValueError
     */
    public function testBytearrayNoMultibyte3()
    {
        bytearray('abcdef', 'UTF-8')[1] = '';
    }

    public function testBytearrayFromHex()
    {
        $this->assertSame(' !"#', (string) bytearray::fromhex('20212223'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBytearrayFromHexInvalid()
    {
        f\bytearray::fromhex('2021222');
    }

    /**
     * @expectedException PythonBuiltins\ValueError
     */
    public function testBytearrayFromHexInvalid2()
    {
        f\bytearray::fromhex('zz');
    }

    public function testDict()
    {
        $stats = [
            ['cat', 12],
            ['dog', 13],
            ['guinea pig', 15]
        ];
        $this->assertEquals([
            'cat' => 12,
            'dog' => 13,
            'guinea pig' => 15,
        ], f\dict($stats));
    }

    public function testDir()
    {
        $obj = new class {
            public $publicProp = 2;
            private $x = 2;
            public function meth1() {}
            public function meth2() {}
        };
        $this->assertEquals(['meth1', 'meth2', 'publicProp'], f\dir($obj));

        $this->assertEquals([], f\dir(false));
        $this->assertEquals([], f\dir([]));
        $this->assertEquals([], f\dir(''));
        $this->assertEquals([], f\dir('a'));
    }

    public function testEnumerate()
    {
        $e = f\enumerate(['a', 'b', 'c']);
        $this->assertInstanceOf(\Generator::class, $e);
        $this->assertEquals([
            [0, 'a'],
            [1, 'b'],
            [2, 'c'],
        ], iterator_to_array($e));
    }

    public function testEnumerateStartAt1()
    {
        $e = f\enumerate(['a', 'b', 'c'], 1);
        $this->assertInstanceOf(\Generator::class, $e);
        $this->assertEquals([
            [1, 'a'],
            [2, 'b'],
            [3, 'c'],
        ], iterator_to_array($e));
    }

    public function testFilter()
    {
        $this->assertEquals(
            [
                1,
                2,
                3,
                4,
            ],
            iterator_to_array(f\filter(null, [0, 1, 2, 3, false, 4]))
        );

        $cb = function ($x) { return $x === 0 || (bool) $x; };
        $this->assertEquals(
            [
                0,
                1,
                2,
                3,
                4,
            ],
            iterator_to_array(f\filter($cb, [0, 1, 2, 3, false, 4]))
        );
    }

    public function testGetattr()
    {
        $this->assertSame(1, f\getattr(new class {
            public $number = 1;
        }, 'number'));
        $this->assertSame(1, f\getattr(new class {
            public $number = 1;
        }, 'doesNotExist', 1));
    }

    public function testHex()
    {
        $this->assertSame('0x20', f\hex(0x20));
    }

    public function testHexNegative()
    {
        $this->assertSame('-0x20', f\hex(-0x20));
    }

    public function testId()
    {
        $this->assertSame(32, strlen(f\id(new \stdClass())));
    }

    public function testMap()
    {
        $this->assertEquals(
            [11, 21],
            iterator_to_array(f\map(
                function ($x, $n) { return $x * 10 + $n; },
                [1, 2],
                1
            ))
        );
    }

    public function testOct()
    {
        $this->assertSame('0o755', f\oct(493));
    }

    public function testReversed() {
        $seq = new \ArrayIterator([1, 2, 3]);
        $this->assertEquals([3, 2, 1], iterator_to_array(f\reversed($seq)));
        $this->assertEquals([3, 2, 1], iterator_to_array(f\reversed($seq, 3)));
    }

    public function testSliceOneArg()
    {
        $slice = slice(10);
        $this->assertSame(10, $slice->stop);
    }

    public function testSliceTwoArgs()
    {
        $slice = slice(10, 20);
        $this->assertSame(10, $slice->start);
        $this->assertSame(20, $slice->stop);
        $this->assertSame(1, $slice->step);
    }

    public function testSliceThreeArgs()
    {
        $slice = slice(10, 20, 2);
        $this->assertSame(10, $slice->start);
        $this->assertSame(20, $slice->stop);
        $this->assertSame(2, $slice->step);
    }

    /**
     * @expectedException ArgumentCountError
     */
    public function testSliceZeroArgs()
    {
        $slice = slice();
    }

    public function testSorted()
    {
        $this->assertEquals(
            [1, 2, 3, 4],
            f\sorted(new \ArrayIterator([4, 3, 2, 1]))
        );
    }

    public function testSortedKey()
    {
        $key = function ($a, $b) {
            return $a - $b;
        };
        $this->assertEquals(
            [1, 2, 3, 4],
            f\sorted(new \ArrayIterator([4, 3, 2, 1]), $key)
        );
    }

    public function testSortedKeyReversed()
    {
        $key = function ($a, $b) {
            return $b - $a;
        };
        $this->assertEquals(
            [1, 2, 3, 4],
            f\sorted(new \ArrayIterator([4, 3, 2, 1]), $key, true)
        );
    }

    public function testStr()
    {
        $this->assertSame('anonymous class', f\str(new class {
            public function __toString() {
                return 'anonymous class';
            }
        }));
    }

    public function testSum()
    {
        $this->assertSame(10, f\sum([2, 2, 2, 2, 2]));
        $this->assertSame(8, f\sum([2, 2, 2, 2, 2], 1));
    }

    public function testZip()
    {
        $labels = ['cat', 'dog', 'pig'];
        $numbers = [12, 13, 15];
        $ret = f\zip($labels, $numbers);
        $this->assertEquals($ret, [
            ['cat', 12],
            ['dog', 13],
            ['pig', 15]
        ]);
    }

    public function testZip2()
    {
        $labels = new \ArrayIterator(['cat', 'dog', 'pig']);
        $numbers = [12, 13, 15];
        $ret = f\zip($labels, $numbers);
        $this->assertEquals($ret, [
            ['cat', 12],
            ['dog', 13],
            ['pig', 15]
        ]);
    }
}
