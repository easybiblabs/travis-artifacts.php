<?php
namespace EasyBib\Test\Travis\Artifacts;

use EasyBib\Travis\Artifacts\PathHandler;
use PHPUnit_Framework_TestCase;

class PathHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testRelativePaths()
    {
        $paths = ['foo', 'bar', 'foobar'];

        $handler = new PathHandler('/root', $paths);

        $this->assertSame(
            ['/root/foo/', '/root/bar/', '/root/foobar/'],
            $handler->getPaths()
        );
    }

    public function testAbsolutePaths()
    {
        $paths = ['foo', 'bar', 'foobar'];

        $handler = new PathHandler('', $paths);

        $this->assertSame(
            ['foo/', 'bar/', 'foobar/'],
            $handler->getPaths()
        );
    }

    public function testTransform()
    {
        $handler = new PathHandler('', ['foo', 'bar']);

        $this->assertSame(
            'bucket/foo/bar/file.jpg',
            $handler->transform('bucket', '/foo/bar/file.jpg')
        );
    }
}
