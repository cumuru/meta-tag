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
namespace Undkonsorten\MetaTag\ViewHelpers;

use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
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
     * @var MetaTagManagerRegistry
     */
    static protected $metaTagManagerRegistry;


    /**
     * Gets the MetaTagRegistry singleton
     *
     * @return MetaTagManagerRegistry
     */
    protected static function getMetaTagManagerRegistry(): MetaTagManagerRegistry
    {
        if (null === static::$metaTagManagerRegistry) {
            static::$metaTagManagerRegistry = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
        }
        return static::$metaTagManagerRegistry;
    }

    /**
     * @codeCoverageIgnore
     */
    public function initializeArguments(): void
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
    protected static function resolveTypeAndName(array $arguments, array $attributeList): array
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
     * @param string $type Type of the meta tag ("property", "http-equiv" or "name")
     * @param string $name Name of the meta tag (i.e. value of the $type attribute)
     * @param string $content Content attribute of the resulting tag
     * @param bool $override If set, overrides an existing tag of same type and name
     */
    protected static function addMetaTag(string $type, string $name, string $content, bool $override = false): void
    {
        $metaTagManager = static::getMetaTagManagerRegistry()->getManagerForProperty($name);
        $metaTagManager->addProperty($name, $content, [], $override, $type);
    }
}
