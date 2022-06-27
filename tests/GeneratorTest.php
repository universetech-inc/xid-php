<?php

namespace Fpay\Xid\Tests;

use Fpay\Xid\Generator;
use Fpay\Xid\Xid;
use Fpay\Xid\Exception;
use TestCase;

class GeneratorTest extends TestCase
{
    public function testCreate()
    {
        $ids = [];
        for ($i = 0; $i < 10; $i++) {
            $ids[$i] = Generator::create();
        }

        for ($i = 1; $i < 10; $i++) {
            $prev = $ids[$i - 1];
            $id = $ids[$i];

            foreach ($ids as $j => $tid) {
                if ($j != $i) {
                    $this->assertNotEquals($id->value, $tid->value, "Generated ID is not unique");
                }
            }

            $secs = $id->time() - $prev->time();
            $this->assertEquals(($secs >= 0 && $secs <= 30), true, "Wrong timestamp in generated ID");

            $this->assertEquals($id->machine(), $prev->machine(), "Machine name is not same");

            $this->assertEquals($id->pid(), $prev->pid(), "Pid is not same");

            $delta = $id->counter() - $prev->counter();
            $this->assertEquals(1, $delta, "Wrong increment in generated ID");
        }
    }

    public function testAPCuCounter()
    {
        $generator = new Generator([
            "cli" => false,
            "apcu" => true,
        ]);

        $ids = [];
        for ($i = 0; $i < 10; $i++) {
            $ids[$i] = $generator->generate();
        }

        for ($i = 1; $i < 10; $i++) {
            $prev = $ids[$i - 1];
            $id = $ids[$i];

            $delta = $id->counter() - $prev->counter();
            $this->assertEquals(1, $delta, "Wrong increment in generated ID");
        }
    }

    public function testNoneAPCuCGICounter()
    {
        $generator = new Generator([
            "cli" => false,
            "apcu" => false,
        ]);

        $ids = [];
        for ($i = 0; $i < 10; $i++) {
            $ids[$i] = $generator->generate();
        }

        for ($i = 1; $i < 10; $i++) {
            $prev = $ids[$i - 1];
            $id = $ids[$i];

            $delta = $id->counter() - $prev->counter();
            $this->assertEquals(1, $delta, "Wrong increment in generated ID");
        }
    }

    public function testFromString()
    {
        for ($i = 0; $i < 10; $i++) {
            $id = Generator::create();
            $fid = Generator::fromString((string) $id);

            $this->assertEquals((string) $id, (string) $fid, "Wrong decode string");
            $this->assertEquals($id->value, $fid->value, "Wrong decode value");
        }
    }

    public function testFromEmptyString()
    {
        $this->expectException(Exception::class);
        Generator::fromString("");
    }

    public function testFromLargeString()
    {
        $this->expectException(Exception::class);
        Generator::fromString("b86oelhmcgq29jqjd91gb86oelhmcgq29jqjd91g");
    }

    public function testFromStringWithInvalidChar()
    {
        $this->expectException(Exception::class);
        Generator::fromString("b86ohmpMcgq29jqjd920");
    }
}
