<?php

namespace Undkonsorten\MetaTag\Service;

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

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class MetaTagRegistry
 * @package Undkonsorten\IntegrationNgfh\Service
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 */
class MetaTagRegistry implements SingletonInterface
{

    /**
     * @var array
     */
    protected $metaTags = [];

    /**
     * @var string
     */
    protected $endingSlash = ' /';

    /**
     * MetaTagRegistry constructor.
     * @param string $endingSlash
     */
    public function __construct($endingSlash = '')
    {
        $this->endingSlash = $endingSlash;
    }

    /**
     * Sets a given meta tag
     *
     * @param string $type The type of the meta tag. Allowed values are property, name or http-equiv
     * @param string $name The name of the property to add
     * @param string $content The content of the meta tag
     * @throws \InvalidArgumentException
     */
    public function setMetaTag(string $type, string $name, string $content)
    {
        /**
         * Lowercase all the things
         */
        $type = strtolower($type);
        $name = strtolower($name);
        if (!in_array($type, ['property', 'name', 'http-equiv'], true)) {
            throw new \InvalidArgumentException(
                'When setting a meta tag the only types allowed are property, name or http-equiv. "' . $type . '" given.',
                1519570773
            );
        }
        $this->metaTags[$type][$name] = $content;
    }

    /**
     * Returns the requested meta tag
     *
     * @param string $type
     * @param string $name
     *
     * @return array
     */
    public function getMetaTag(string $type, string $name): array
    {
        /**
         * Lowercase all the things
         */
        $type = strtolower($type);
        $name = strtolower($name);
        if (isset($this->metaTags[$type], $this->metaTags[$type][$name])) {
            return [
                'type' => $type,
                'name' => $name,
                'content' => $this->metaTags[$type][$name]
            ];
        }
        return [];
    }

    /**
     * Unset the requested meta tag
     *
     * @param string $type
     * @param string $name
     */
    public function removeMetaTag(string $type, string $name)
    {
        /**
         * Lowercase all the things
         */
        $type = strtolower($type);
        $name = strtolower($name);
        unset($this->metaTags[$type][$name]);
    }

    /**
     * Renders metaTags
     *
     * @return array
     */
    public function renderMetaTags(): array
    {
        $metaTags = [];
        foreach ($this->metaTags as $metaTagType => $type) {
            foreach ($type as $metaType => $content) {
                $metaTags[] = '<meta ' . htmlspecialchars($metaTagType) . '="' . htmlspecialchars($metaType) . '" content="' . htmlspecialchars($content) . '"' . $this->endingSlash . '>';
            }
        }
        return $metaTags;
    }

}
