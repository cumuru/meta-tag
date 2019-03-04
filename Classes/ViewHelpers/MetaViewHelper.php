<?php
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
declare(strict_types=1);
namespace Undkonsorten\MetaTag\ViewHelpers;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class MetaTagViewHelper
 *
 * @package Undkonsorten\MetaTag\ViewHelpers
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 */
class MetaViewHelper extends AbstractViewHelper
{

    use CompileWithRenderStatic;

    /**
     * @codeCoverageIgnore
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('property', 'string', '"property" attribute of the meta tag');
        $this->registerArgument('http-equiv', 'string', '"http-equiv" attribute of the meta tag');
        $this->registerArgument('name', 'string', '"name" attribute of the meta tag');
        $this->registerArgument('content', 'string',
            'Content of the meta tag. If not provided tag content will be used. If both are empty no meta tag will be rendered at all');
        $this->registerArgument('override', 'bool', 'Existing meta tags will be overridden if set', false, false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        list($type, $name) = static::resolveTypeAndName($arguments, ['property', 'http-equiv', 'name']);
        // Use content attribute with fallback on tag content
        $content = trim((string)($arguments['content'] ?? $renderChildrenClosure()));
        // Only add meta tag if there‘s content to add
        if (strlen($content)) {
            $override = (bool) $arguments['override'];
            static::addMetaTag($type, $name, $content, $override);
        }
    }

    /**
     * Makes sure exactly one of the allowed properties is set and returns type and name
     *
     * @param array $arguments
     * @param array $attributeList
     * @return array
     */
    protected static function resolveTypeAndName(array $arguments, array $attributeList)
    {
        $typeArray = array_filter($attributeList, function(string $attribute) use ($arguments) {
            return isset($arguments[$attribute]);
        });
        $count = count($typeArray);
        if ($count === 0) {
            throw new \InvalidArgumentException('Exactly one of the attributes "property","http-equiv" and "name" needs to be set.',
                1519645772);
        } elseif ($count > 1) {
            throw new \InvalidArgumentException(sprintf('Only one of the attributes "property","http-equiv" and "name" can be set at the same time. Attributes "%s" given', implode(', ', $typeArray)),
                1519645296);
        }
        $type = array_pop($typeArray);
        $name = $arguments[$type];
        return [$type, $name];
    }

    /**
     * Adds a meta tag of given type, name and content
     *
     * Uses custom {@see \Undkonsorten\MetaTag\Page\PageRenderer} for now, can be replaced by
     * PageRenderer calls from TYPO3 v9 on
     *
     * @param string $type Type of the meta tag ("property", "http-equiv" or "name")
     * @param string $name Name of the meta tag (i.e. value of the $type attribute)
     * @param string $content Content attribute of the resulting tag
     * @param bool $override If set, overrides an existing tag of same type and name
     */
    protected static function addMetaTag(string $type, string $name, string $content, bool $override = false)
    {
        /** @var \Undkonsorten\MetaTag\Page\PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $metaTags = $pageRenderer->getMetaTags();

        // Check if the new meta tag should be added
        $shouldAddNewMetaTag = true;
        $metaTags = array_filter($metaTags, function ($metaTag) use ($type, $name, $override, &$shouldAddNewMetaTag) {
            if (!static::metaTagAlreadyExists($metaTag, $type, $name)) {
                if ($override) {
                    return false;
                } else {
                    $shouldAddNewMetaTag = false;
                }
            }
            return true;
        });

        // Add new meta tag
        if ($shouldAddNewMetaTag) {
            $metaTags[] = static::renderMetaTag($type, $name, $content);
        }

        $pageRenderer->setMetaTags($metaTags);
    }

    /**
     * Check if meta tag already exists
     *
     * @param string $metaTag Rendered meta tag
     * @param string $type Type of the meta tag ("property", "http-equiv" or "name")
     * @param string $name Name of the meta tag (i.e. value of the $type attribute)
     * @return bool `true` if the meta tag already exists, `false` otherwise
     */
    public static function metaTagAlreadyExists(string $metaTag, string $type, string $name): bool
    {
        return stripos($metaTag, sprintf('%s="%s"', $type, $name)) === false;
    }

    /**
     * Renders a meta tag
     * 
     * @param string $type Type of the meta tag ("property", "http-equiv" or "name")
     * @param string $name Name of the meta tag (i.e. value of the $type attribute)
     * @param string $content Content attribute of the resulting tag
     * @return string The rendered meta tag
     */
    public static function renderMetaTag(string $type, string $name, string $content): string
    {
        /** @noinspection HtmlUnknownAttribute */
        return sprintf(
            '<meta %s="%s" content="%s" />',
            htmlspecialchars($type),
            htmlspecialchars($name),
            htmlspecialchars($content)
        );
    }
}
