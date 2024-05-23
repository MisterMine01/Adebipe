<?php

use Adebipe\Services\Renderer;
use PHPUnit\Framework\AdebipeCoreTestCase;

class RendererTest extends AdebipeCoreTestCase
{
    public function testRenderer()
    {
        $renderer = new Renderer();
        $dir = getProperty($renderer, '_dir');
        $this->assertEquals('tests/other/src/templates/', $dir);
    }

    public function testTemplate()
    {
        $renderer = new Renderer();
        $result = invokeMethod($renderer, '_getTemplate', ["test.php", []]);
        $this->assertEquals('Hello World', $result);
    }

    public function testTemplateWithVar()
    {
        $renderer = new Renderer();
        $result = invokeMethod($renderer, '_getTemplate', ["test.php", ["name" => "Adebipe"]]);
        $this->assertEquals('Hello Adebipe', $result);
    }

    public function testRender()
    {
        $renderer = new Renderer();
        $result = $renderer->render("test.php", ["name" => "Adebipe"]);
        $this->assertEquals('Hello Adebipe', $result->body);
        $this->assertEquals(200, $result->status);
    }

    public function testRenderWithNoFile()
    {
        $renderer = new Renderer();
        $result = $renderer->render("test2.php", ["name" => "Adebipe"]);
        $this->assertEquals(500, $result->status);
        $this->assertEquals('Internal Server Error', $result->body);
    }
}
