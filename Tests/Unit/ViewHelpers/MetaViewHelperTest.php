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
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use Undkonsorten\MetaTag\Page\PageRenderer;
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
    protected $pageRendererMock;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->pageRendererMock = $this->getMockBuilder(PageRenderer::class)->disableOriginalConstructor()->getMock();
        $this->viewHelper = $this->getAccessibleMock(MetaViewHelper::class, ['none']);
        $this->viewHelper->_setStatic('pageRenderer', $this->pageRendererMock);
        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        unset($this->viewHelper);
        unset($this->renderingContext);
        unset($this->pageRendererMock);
    }

    /**
     * @param string|null $result
     * @return \Closure
     */
    protected function getRenderChildrenClosureForExpectedResult($result = null): \Closure
    {
        return function() use ($result) {
            return $result;
        };
    }

    /**
     * @param $expectsGetMetaTagsCall
     * @param $getMetaTagsReturns
     * @param $expectsSetMetaTagsCall
     */
    protected function setNewPageRendererMockInViewHelper($expectsGetMetaTagsCall, $getMetaTagsReturns, $expectsSetMetaTagsCall)
    {
        $pageRendererMock = $this->getMockBuilder(PageRenderer::class)->disableOriginalConstructor()->getMock();
        $pageRendererMock->expects($expectsGetMetaTagsCall ? $this->once() : $this->never())
            ->method('getMetaTags')
            ->willReturn($getMetaTagsReturns);
        $pageRendererMock->expects($expectsSetMetaTagsCall ? $this->once() : $this->never())
            ->method('setMetaTags');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->viewHelper->_setStatic('pageRenderer', $pageRendererMock);
    }

    /**
     * @test
     * @dataProvider moreThanOneAllowedArgument
     *
     * @param array $arguments
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
        $this->pageRendererMock->expects($this->never())->method('setMetaTags');
        $this->viewHelper::renderStatic(
            ['content' => '', 'property' => 'test'],
            $this->getRenderChildrenClosureForExpectedResult(''),
            $this->renderingContext
        );
    }

    /**
     * @test
     * @dataProvider overrideDataProvider
     *
     * @param $firstContent
     * @param $firstOverride
     * @param $secondContent
     * @param $secondOverride
     * @param $callsSetterOnFirst
     * @param $callsSetterOnSecond
     */
    public function overrideAttributeRaisesPriority($firstContent, $firstOverride, $secondContent, $secondOverride, $callsSetterOnFirst, $callsSetterOnSecond)
    {
        $hasFirstContent = (bool)strlen($firstContent);
        $this->setNewPageRendererMockInViewHelper($hasFirstContent, [], $callsSetterOnFirst);
        $this->viewHelper::renderStatic(
            [
                'property' => 'test',
                'override' => $firstOverride,
                'content' => $firstContent,
            ],
            $this->getRenderChildrenClosureForExpectedResult(),
            $this->renderingContext
        );

        $hasSecondContent = (bool)strlen($secondContent);
        $this->setNewPageRendererMockInViewHelper(
            $hasSecondContent,
            $hasFirstContent
                ? [sprintf('<meta property="test" content="%s" />', htmlspecialchars($firstContent))]
                : [],
            $callsSetterOnSecond
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
    }

    /**
     * @test
     * @dataProvider contentAttributeAndTagContent
     *
     * @param $contentAttribute
     * @param $tagContent
     * @param $expected
     */
    public function contentAttributeAndTagContentWillRenderTag($contentAttribute, $tagContent, $expected)
    {
        $arguments = ['property' => 'test'];
        if (null !== $contentAttribute) {
            $arguments['content'] = $contentAttribute;
        }
        $this->pageRendererMock->expects($this->once())->method('setMetaTags')->with([
            sprintf('<meta property="test" content="%s" />', htmlspecialchars($expected))
        ]);
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
            ['content1', false, 'content2', true, true, true],
            ['content1', false, 'content2', false, true, false],
            ['content1', true, 'content2', true, true, true],
            ['content1', true, 'content2', false, true, false],
            ['', false, 'content2', false, false, true],
            ['content1', false, '', true, true, false],
        ];
    }
}
