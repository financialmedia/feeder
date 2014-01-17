<?php

namespace FM\Feeder\Tests\Transport\Matcher;

use FM\Feeder\Transport\Matcher\CallbackMatcher;

class CallbackMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \FM\Feeder\Exception\UnexpectedTypeException
     */
    public function testUnexpectedType()
    {
        new CallbackMatcher('foo');
    }

    public function testMatch()
    {
        $matcher = new CallbackMatcher(function (array $files) {
            return reset($files);
        });

        $this->assertSame('foo', $matcher->match(['foo', 'bar', 'baz']));
    }

    public function testToString()
    {
        $matcher = new CallbackMatcher(function (array $files) {
            return reset($files);
        });

        $this->assertSame('callback', (string) $matcher);
    }
}
