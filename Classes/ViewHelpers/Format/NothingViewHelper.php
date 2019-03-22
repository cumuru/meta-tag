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
namespace Undkonsorten\MetaTag\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class NothingViewHelper
 *
 * @package Undkonsorten\MetaTag\ViewHelpers\Format
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 */
class NothingViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    static public function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $renderChildrenClosure();
    }
}
