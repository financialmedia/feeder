<?php

namespace FM\Feeder\Tests\Reader;

use FM\Feeder\Reader\XmlReader;
use FM\Feeder\Resource\StringResource;

class XmlReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCurrentXml()
    {
        $reader = new XmlReader('bar', new StringResource('<foo><bar>baz</bar></foo>'));
        $this->assertSame('<bar>baz</bar>', $reader->current());

        return $reader;
    }

    public function testReadXml()
    {
        $reader = new XmlReader('test', new StringResource('<foo><test>foo</test><test>bar</test><test>baz</test></foo>'));

        foreach (['foo', 'bar', 'baz'] as $test) {
            $bag = $reader->read();
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\ParameterBag', $bag);
            $this->assertSame($test, $bag->get(0));
        }

        // no more items left
        $this->assertNull($reader->read());
    }

    /**
     * @expectedException        FM\Feeder\Exception\ReadException
     * @expectedExceptionMessage Opening and ending tag mismatch
     */
    public function testCurrentOnInvalidXml()
    {
        $reader = new XmlReader('bar', new StringResource('<foo><baz/><bar></foo>'));
        $reader->current();
    }

    /**
     * @expectedException        FM\Feeder\Exception\ReadException
     * @expectedExceptionMessage Opening and ending tag mismatch
     */
    public function testReadOnInvalidXml()
    {
        $reader = new XmlReader('bar', new StringResource('<foo><bar/><bar></foo>'));
        $reader->read();
    }

    /**
     * @expectedException        FM\Feeder\Exception\ReadException
     * @expectedExceptionMessage Opening and ending tag mismatch
     */
    public function testNextOnInvalidXml()
    {
        $reader = new XmlReader('bar', new StringResource('<foo><bar></foo>'));
        $reader->next();
    }
}
