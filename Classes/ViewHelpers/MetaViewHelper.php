<?php

namespace Undkonsorten\MetaTag\ViewHelpers;

/**
 * This file is part of the TYPO3 CMS project.
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Undkonsorten\MetaTag\Service\MetaTagRegistry;


/**
 * Class MetaTagViewHelper
 * @package Undkonsorten\IntegrationNgfh\ViewHelpers
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 */
class MetaViewHelper extends AbstractViewHelper
{

    use CompileWithRenderStatic;

    /**
     * can be removed in TYPO3 v9
     *
     * @var MetaTagRegistry
     */
    static protected $metaTagRegistry;


    /**
     * Gets the MetaTagRegistry singleton
     * can be removed in TYPO3 v9, replaced by PageRenderer
     *
     * @return MetaTagRegistry
     */
    static protected function getMetaTagRegistry()
    {
        if (null === static::$metaTagRegistry) {
            static::$metaTagRegistry = GeneralUtility::makeInstance(ObjectManager::class)->get(MetaTagRegistry::class);
        }
        return static::$metaTagRegistry;
    }

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
    static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        list($type, $name) = static::resolveTypeAndName($arguments, ['property', 'http-equiv', 'name']);
        // Use content attribute with fallback on tag content
        $content = trim($arguments['content'] ?? $renderChildrenClosure());
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
    static protected function resolveTypeAndName(array $arguments, array $attributeList)
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
     * Calls custom Registry for now, can be replaced by
     * PageRenderer calls from TYPO3 v9 on
     *
     * @param string $type Type of the meta tag ("property", "http-equiv" or "name")
     * @param string $name Name of the meta tag (i.e. value of the $type attribute)
     * @param string $content Content attribute of the resulting tag
     * @param bool $override If set, overrides an existing tag of same type and name
     */
    static protected function addMetaTag(string $type, string $name, string $content, bool $override = false)
    {
        $metaTagRegistry = static::getMetaTagRegistry();
        if (!count($metaTagRegistry->getMetaTag($type, $name))) {
            $metaTagRegistry->setMetaTag($type, $name, $content);
        } elseif ($override) {
            $metaTagRegistry->removeMetaTag($type, $name);
            $metaTagRegistry->setMetaTag($type, $name, $content);
        }
    }
}
