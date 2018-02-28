<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 27.02.18
 * Time: 13:08
 */

namespace Undkonsorten\MetaTag\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Undkonsorten\MetaTag\Service\MetaTagRegistry;

class MetaTagRegistryTest extends UnitTestCase

{

    /**
     * @var MetaTagRegistry
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = GeneralUtility::makeInstance(MetaTagRegistry::class);
    }

    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function unknownTypesAreNotAllowed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->fixture->setMetaTag('unknownType', 'name', 'value');
    }

    /**
     * @test
     */
    public function setMetaTag()
    {
        $this->fixture->setMetaTag('property', 'test', 'value');
        $this->assertCount(1, $this->fixture->renderMetaTags());
    }

    /**
     * @test
     * @depends setMetaTag
     */
    public function renderMetaTags()
    {
        $expected = ['<meta property="test" content="value">'];
        $this->assertEquals($expected, $this->fixture->renderMetaTags());
    }

    /**
     * @test
     * @depends setMetaTag
     */
    public function getMetaTag()
    {
        $expected = [
            'type' => 'property',
            'name' => 'test',
            'content' => 'value',
        ];
        $this->assertEquals($expected, $this->fixture->getMetaTag('property', 'test'));

    }


    /**
     * @test
     * @depends setMetaTag
     */
    public function removeMetaTag()
    {
        $this->assertCount(1, $this->fixture->renderMetaTags());
        $this->fixture->removeMetaTag('property', 'test');
        $this->assertEmpty($this->fixture->renderMetaTags());
    }

}
