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
namespace Undkonsorten\MetaTag\Tests\Unit\ViewHelpers\Format;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use Undkonsorten\MetaTag\ViewHelpers\Format\NothingViewHelper;

class NothingViewHelperTest extends UnitTestCase
{
    /**
     * @var NothingViewHelper
     */
    protected $viewHelper;

    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    public function setUp(): void
    {
        $this->viewHelper = $this->getAccessibleMock(NothingViewHelper::class, ['none']);
        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)->disableOriginalConstructor()->getMock();
    }

    public function tearDown(): void
    {
        unset($this->viewHelper);
        unset($this->renderingContext);
    }

    /**
     * @param $content
     *
     * @test
     * @dataProvider contentProvider
     */
    public function tagContentIsIgnored($content): void
    {
        $result = $this->viewHelper::renderStatic([], function () use ($content) {
            return $content;
        }, $this->renderingContext);
        $this->assertNull($result);
    }

    public function contentProvider(): array
    {
        return [
            'String content' => ['My string'],
            'Array' => [['An array']],
            'Null' => [null],
            'Closure' => [
                function () {
                }
            ],
            'Object' => [(new \StdClass())],
        ];
    }

}
