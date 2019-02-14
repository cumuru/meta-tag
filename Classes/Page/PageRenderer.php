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
namespace Undkonsorten\MetaTag\Page;

/**
 * Custom PageRenderer for better handling of meta tags
 *
 * @package Undkonsorten\MetaTag\Page
 * @author Elias Häußler <elias.haeussler@undkonsorten.com>
 */
class PageRenderer extends \TYPO3\CMS\Core\Page\PageRenderer
{

    /**
     * @return array
     */
    public function getMetaTags(): array
    {
        return $this->metaTags;
    }

    /**
     * @param array $metaTags
     * @return self
     */
    public function setMetaTags(array $metaTags): self
    {
        $this->metaTags = $metaTags;
        return $this;
    }

}
