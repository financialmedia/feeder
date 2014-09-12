<?php

namespace FM\Feeder;

class FeedTest extends \PHPUnit_Framework_TestCase
{
    public function testHasModifierAt()
    {
        $reader = $this->getMockBuilder('FM\Feeder\Reader\ReaderInterface')->getMockForAbstractClass();
        $modifier = $this->getMockBuilder('FM\Feeder\Modifier\Item\ModifierInterface')->getMockForAbstractClass();

        $feed = new Feed($reader);

        $this->assertFalse($feed->hasModifierAt(1));

        $feed->addModifier($modifier, 1);

        $this->assertTrue($feed->hasModifierAt(1));
    }

    public function testRemoveModifier()
    {
        $reader = $this->getMockBuilder('FM\Feeder\Reader\ReaderInterface')->getMockForAbstractClass();
        $modifier = $this->getMockBuilder('FM\Feeder\Modifier\Item\ModifierInterface')->getMockForAbstractClass();

        $feed = new Feed($reader);
        $feed->addModifier($modifier, 1);

        $this->assertTrue($feed->hasModifierAt(1));

        $feed->removeModifier($modifier);

        $this->assertFalse($feed->hasModifierAt(1));
    }

    public function testRemoveModifierAt()
    {
        $reader = $this->getMockBuilder('FM\Feeder\Reader\ReaderInterface')->getMockForAbstractClass();
        $modifier = $this->getMockBuilder('FM\Feeder\Modifier\Item\ModifierInterface')->getMockForAbstractClass();

        $feed = new Feed($reader);
        $feed->addModifier($modifier, 1);

        $this->assertTrue($feed->hasModifierAt(1));

        $feed->removeModifierAt(1);

        $this->assertFalse($feed->hasModifierAt(1));
    }
}
