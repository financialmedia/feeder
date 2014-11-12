<?php

namespace FM\Feeder\Tests\Resource\Transformer;

use FM\Feeder\Resource\ResourceCollection;
use FM\Feeder\Resource\StringResource;
use FM\Feeder\Resource\Transformer\OverwriteXmlDeclarationTransformer;

class OverwriteXmlDeclarationTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testTransform($from, $to)
    {
        $resource = new StringResource($from);
        $coll = new ResourceCollection([$resource]);
        $coll->addTransformer(new OverwriteXmlDeclarationTransformer());

        $file = $coll->current()->getFile()->getPathname();

        $this->assertSame($to, file_get_contents($file));
    }

    public static function getTestData()
    {
        return [
            [
                '<?xml version="1.0" encoding="ISO-8859-1"?>
                <root></root>',
                '<?xml version="1.0" encoding="UTF-8"?>
                <root></root>',
            ],
            [
                '<root></root>',
                '<root></root>',
            ],
            [
                '<?xml version="1.0" encoding="UTF-16"?>
                <root></root>',
                '<?xml version="1.0" encoding="UTF-8"?>
                <root></root>',
            ]
        ];
    }
}
