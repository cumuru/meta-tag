<?php
defined('TYPO3_MODE') or die();

// Register custom page renderer
call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Page\PageRenderer::class] = [
        'className' => \Undkonsorten\MetaTag\Page\PageRenderer::class,
    ];
});
