<?php

namespace Enum;

use App\Enum\ProjectRendererEnum;
use PHPUnit\Framework\TestCase;

class ProjectRendererEnumTest extends TestCase
{
    public function testGetChoices()
    {
        $choices = ProjectRendererEnum::getChoices();
        $this->assertIsArray($choices);
        $this->assertArrayHasKey('Simple panorama', $choices);
        $this->assertArrayHasKey('Gallery', $choices);
        $this->assertArrayHasKey('Virtual visit', $choices);
        $this->assertEquals('simple_panorama', $choices['Simple panorama']);
        $this->assertEquals('gallery', $choices['Gallery']);
        $this->assertEquals('virtual_visit', $choices['Virtual visit']);
    }
}
