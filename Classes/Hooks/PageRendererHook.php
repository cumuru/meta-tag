<?php

namespace Undkonsorten\MetaTag\Hooks;

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

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Undkonsorten\MetaTag\Service\MetaTagRegistry;

/**
 * Class PageRendererHook
 * @package Undkonsorten\IntegrationNgfh\Hooks
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 */
class PageRendererHook
{

    /**
     * @var MetaTagRegistry
     */
    protected $metaTagRegistry;

    /**
     * @return MetaTagRegistry
     */
    protected function getMetaTagRegistry()
    {
        if (null === $this->metaTagRegistry) {
            $this->metaTagRegistry = GeneralUtility::makeInstance(ObjectManager::class)->get(MetaTagRegistry::class);
        }
        return $this->metaTagRegistry;
    }

    /**
     * @param array $params
     * @param PageRenderer $pageRenderer
     */
    public function transferMetaTagsToPageRenderer(
        /* @noinspection PhpUnusedParameterInspection */ array &$params,
        PageRenderer &$pageRenderer
    ) {
        foreach ($this->getMetaTagRegistry()->renderMetaTags() as $metaTag) {
            $pageRenderer->addMetaTag($metaTag);
        }
    }

}
