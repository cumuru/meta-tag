<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 27.02.18
 * Time: 13:46
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

    public function setUp()
    {
        $this->viewHelper = $this->getAccessibleMock(NothingViewHelper::class, ['none']);
        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        unset($this->viewHelper);
        unset($this->renderingContext);
    }

    /**
     * @test
     * @dataProvider contentProvider
     */
    public function tagContentIsIgnored($content)
    {
        $result = $this->viewHelper::renderStatic([], function () {
            return 'Long content';
        }, $this->renderingContext);
        $this->assertNull($result);
    }

    public function contentProvider()
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
