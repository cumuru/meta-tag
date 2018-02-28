<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 27.02.18
 * Time: 10:05
 */

namespace Undkonsorten\MetaTag\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use Undkonsorten\MetaTag\Service\MetaTagRegistry;
use Undkonsorten\MetaTag\ViewHelpers\MetaViewHelper;

class MetaViewHelperTest extends UnitTestCase
{

    /**
     * @var MetaViewHelper
     */
    protected $viewHelper;

    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    /**
     * @var MockObject
     */
    protected $metaTagRegistry;

    public function setUp()
    {
        $this->metaTagRegistry = $this->getMockBuilder(MetaTagRegistry::class)->setMethods(['setMetaTag'])->getMock();
        $this->viewHelper = $this->getAccessibleMock(MetaViewHelper::class, ['none']);
        $this->viewHelper->_setStatic('metaTagRegistry', $this->metaTagRegistry);
        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        unset($this->viewHelper);
        unset($this->renderingContext);
        unset($this->metaTagRegistry);
    }

    protected function getRenderChildrenClosureForExpectedResult($result = null)
    {
        return function() use ($result) {
            return $result;
        };
    }

    /**
     * @test
     * @dataProvider moreThanOneAllowedArgument
     */
    public function exactlyOneOfTheExpectedArgumentsShouldBeSet(array $arguments)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->viewHelper::renderStatic(
            $arguments,
            $this->getRenderChildrenClosureForExpectedResult(),
            $this->renderingContext
        );
    }

    /**
     * @test
     */
    public function exactlyOneOfTheExpectedArgumentsMustBeSet()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->viewHelper::renderStatic(
            [],
            $this->getRenderChildrenClosureForExpectedResult(),
            $this->renderingContext
        );
    }

    /**
     * @test
     */
    public function emptyContentAttributeWillNotRenderATag()
    {
        $this->metaTagRegistry->expects($this->never())->method('setMetaTag');
        $this->viewHelper::renderStatic(
            ['content' => '', 'property' => 'test'],
            $this->getRenderChildrenClosureForExpectedResult(''),
            $this->renderingContext
        );
    }

    /**
     * @param $firstContent
     * @param $firstOverride
     * @param $secondContent
     * @param $secondOverride
     *
     * @test
     * @dataProvider overrideDataProvider
     */
    public function overrideAttributeRaisesPriority($firstContent, $firstOverride, $secondContent, $secondOverride, $expectedContent)
    {
        $metaTagRegistry = new MetaTagRegistry;
        $this->viewHelper->_setStatic('metaTagRegistry', $metaTagRegistry);
        $this->viewHelper::renderStatic(
            [
                'property' => 'test',
                'override' => $firstOverride,
                'content' => $firstContent,
            ],
            $this->getRenderChildrenClosureForExpectedResult(),
            $this->renderingContext
        );
        $this->viewHelper::renderStatic(
            [
                'property' => 'test',
                'override' => $secondOverride,
                'content' => $secondContent,
            ],
            $this->getRenderChildrenClosureForExpectedResult(),
            $this->renderingContext
        );
        $resultingTag = $metaTagRegistry->getMetaTag('property', 'test');
        $this->assertEquals($expectedContent, $resultingTag['content']);
//        DebuggerUtility::var_dump($result, __METHOD__, 8, true);
    }

    /**
     * @test
     * @dataProvider contentAttributeAndTagContent
     * @param $contentAttribute
     * @param $tagContent
     * @param $expected
     */
    public function contentAttributeAndTagContentWillRenderTag($contentAttribute, $tagContent, $expected)
    {
        $this->metaTagRegistry->expects($this->once())->method('setMetaTag')->with('property', 'test', $expected);
        $arguments = ['property' => 'test'];
        if (null !== $contentAttribute) {
            $arguments['content'] = $contentAttribute;
        }
        $this->viewHelper::renderStatic(
            $arguments,
            $this->getRenderChildrenClosureForExpectedResult($tagContent),
            $this->renderingContext
        );
    }

    public function moreThanOneAllowedArgument()
    {
        return [
            'Property and name' => [['property' => 'a', 'name' => 'b']],
            'Property and http-equiv' => [['property' => 'a', 'http-equiv' => 'b']],
            'Name and http-equiv' => [['name' => 'a', 'http-equiv' => 'b']],
            'All three' => [['property' => 'a', 'name' => 'b', 'http-equiv' => 'c']],
        ];
    }

    public function contentAttributeAndTagContent()
    {
        return [
            'Content attribute' => ['attribute', null, 'attribute'],
            'Tag content' => [null, 'tag content', 'tag content'],
            'Content attribute overrides tag content' => ['attribute', 'tag content', 'attribute'],
        ];
    }

    public function overrideDataProvider()
    {
        return [
            ['content1', false, 'content2', true, 'content2'],
            ['content1', false, 'content2', false, 'content1'],
            ['content1', true, 'content2', true, 'content2'],
            ['content1', true, 'content2', false, 'content1'],
            ['', false, 'content2', false, 'content2'],
            ['content1', false, '', true, 'content1'],
        ];
    }
}
