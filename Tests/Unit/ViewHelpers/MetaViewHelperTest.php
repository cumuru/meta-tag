<?php
declare(strict_types=1);
/**
 * This file is part of the TYPO3 CMS extension "meta_tag".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
namespace Undkonsorten\MetaTag\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerInterface;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
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
    protected $metaTagManagerRegistry;

    /**
     * @var MockObject
     */
    protected $metaTagManager;

    public function setUp(): void
    {
        $this->metaTagManagerRegistry = $this->getMockBuilder(MetaTagManagerRegistry::class)->setMethods(['getManagerForProperty'])->getMock();
        $this->metaTagManager = $this->getMockBuilder(MetaTagManagerInterface::class)
            ->setMethods([
                'addProperty',
                'getProperty',
                'canHandleProperty',
                'getAllHandledProperties',
                'renderAllProperties',
                'renderProperty',
                'removeProperty',
                'removeAllProperties'
            ])
            ->getMock();
        $this->metaTagManagerRegistry->expects($this->any())->method('getManagerForProperty')->willReturn($this->metaTagManager);

        $this->viewHelper = $this->getAccessibleMock(MetaViewHelper::class, ['none']);
        $this->viewHelper->_setStatic('metaTagManagerRegistry', $this->metaTagManagerRegistry);
        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)->disableOriginalConstructor()->getMock();
    }

    public function tearDown(): void
    {
        unset($this->viewHelper);
        unset($this->renderingContext);
        unset($this->metaTagManagerRegistry);
    }

    /**
     * @param null $result
     * @return \Closure
     */
    protected function getRenderChildrenClosureForExpectedResult($result = null): \Closure
    {
        return function() use ($result) {
            return $result;
        };
    }

    /**
     * @param array $arguments
     *
     * @test
     * @dataProvider moreThanOneAllowedArgument
     */
    public function exactlyOneOfTheExpectedArgumentsShouldBeSet(array $arguments): void
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
    public function exactlyOneOfTheExpectedArgumentsMustBeSet(): void
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
    public function emptyContentAttributeWillNotRenderATag(): void
    {
        $this->metaTagManager->expects($this->never())->method('addProperty');
        $this->viewHelper::renderStatic(
            ['content' => '', 'property' => 'test'],
            $this->getRenderChildrenClosureForExpectedResult(''),
            $this->renderingContext
        );
    }

    /**
     * @param string $type
     * @param string $property
     * @param string $content
     * @param bool $override
     * @param bool $expectedReplaceArgument
     *
     * @test
     * @dataProvider overrideDataProvider
     */
    public function overrideAttributeRaisesPriority(
        string $type,
        string $property,
        string $content,
        bool $override,
        bool $expectedReplaceArgument
    ): void
    {
        $this->metaTagManager->expects($this->once())->method('addProperty')
            ->with($property, $content, [], $expectedReplaceArgument, $type);
        $this->viewHelper::renderStatic(
            [
                $type => $property,
                'override' => $override,
                'content' => $content,
            ],
            $this->getRenderChildrenClosureForExpectedResult(),
            $this->renderingContext
        );
    }

    /**
     * @param string|null $contentAttribute
     * @param string|null $tagContent
     * @param string $expected
     *
     * @test
     * @dataProvider contentAttributeAndTagContent
     */
    public function contentAttributeAndTagContentWillRenderTag(
        ?string $contentAttribute,
        ?string $tagContent,
        string $expected
    ): void
    {
        $arguments = ['property' => 'test'];
        if (null !== $contentAttribute) {
            $arguments['content'] = $contentAttribute;
        }
        $this->metaTagManager->expects($this->once())->method('addProperty')
            ->with('test', $expected, [], false, 'property');
        $this->viewHelper::renderStatic(
            $arguments,
            $this->getRenderChildrenClosureForExpectedResult($tagContent),
            $this->renderingContext
        );
    }

    public function moreThanOneAllowedArgument(): array
    {
        return [
            'Property and name' => [['property' => 'a', 'name' => 'b']],
            'Property and http-equiv' => [['property' => 'a', 'http-equiv' => 'b']],
            'Name and http-equiv' => [['name' => 'a', 'http-equiv' => 'b']],
            'All three' => [['property' => 'a', 'name' => 'b', 'http-equiv' => 'c']],
        ];
    }

    public function contentAttributeAndTagContent(): array
    {
        return [
            'Content attribute' => ['attribute', null, 'attribute'],
            'Tag content' => [null, 'tag content', 'tag content'],
            'Content attribute overrides tag content' => ['attribute', 'tag content', 'attribute'],
        ];
    }

    public function overrideDataProvider(): array
    {
        return [
            'With override and type name' => ['name', 'description', 'content', true, true],
            'With override and type property' => ['property', 'og:image', 'content', true, true],
            'With override and type http-equiv' => ['http-equiv', 'refresh', 'content', true, true],
            'Without override and type name' => ['name', 'description', 'content', false, false],
            'Without override and type property' => ['property', 'og:image', 'content', false, false],
            'Without override and type http-equiv' => ['http-equiv', 'refresh', 'content', false, false],
        ];
    }
}
