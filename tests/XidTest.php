<?php

namespace Fpay\Xid\Tests;

use Fpay\Xid\Xid;
use TestCase;

class XidTest extends TestCase
{
    public function getIdCases()
    {
        return [
            [
                "id" => [0x4d, 0x88, 0xe1, 0x5b, 0x60, 0xf4, 0x86, 0xe4, 0x28, 0x41, 0x2d, 0xc9],
                "timestamp" => 1300816219,
                "machine" => [0x60, 0xf4, 0x86],
                "pid" => 0xe428,
                "counter" => 4271561,
            ],
            [
                "id" => [0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00],
                "timestamp" => 0,
                "machine" => [0x00, 0x00, 0x00],
                "pid" => 0x0000,
                "counter" => 0,
            ],
            [
                "id" => [0x00, 0x00, 0x00, 0x00, 0xaa, 0xbb, 0xcc, 0xdd, 0xee, 0x00, 0x00, 0x01],
                "timestamp" => 0,
                "machine" => [0xaa, 0xbb, 0xcc],
                "pid" => 0xddee,
                "counter" => 1,
            ],
        ];
    }

    public function testXidToString()
    {
        $id = new Xid([0x4d, 0x88, 0xe1, 0x5b, 0x60, 0xf4, 0x86, 0xe4, 0x28, 0x41, 0x2d, 0xc9]);
        $this->assertEquals("9m4e2mr0ui3e8a215n4g", (string) $id, "Encoded xid string should equals");
    }

    public function testXidPartsExtraction()
    {
        $cases = $this->getIdCases();
        foreach ($cases as $i => $case) {
            $id = new Xid($case["id"]);
            $this->assertEquals($case["timestamp"], $id->time(), "case {$i}: time should equals");
            $this->assertEquals(implode(array_map(function($v) { return chr($v); }, $case["machine"])), $id->machine(), "case {$i}: machine should equals");
            $this->assertEquals($case["pid"], $id->pid(), "case {$i}: pid should equals");
            $this->assertEquals($case["counter"], $id->counter(), "case {$i}: counter should equals");
        }
    }
}
