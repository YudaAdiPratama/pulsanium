<?php

namespace Tests\Unit;

use App\Services\OperatorDetector;
use PHPUnit\Framework\TestCase;

class OperatorDetectorTest extends TestCase
{
    public function test_it_detects_operator_and_maps_byu_to_telkomsel_products(): void
    {
        $detector = new OperatorDetector;

        $this->assertSame(['name' => 'Telkomsel', 'database' => 'TELKOMSEL'], $detector->detect('081234567890'));
        $this->assertSame(['name' => 'BYU', 'database' => 'TELKOMSEL'], $detector->detect('085112345678'));
        $this->assertNull($detector->detect('080012345678'));
    }
}
