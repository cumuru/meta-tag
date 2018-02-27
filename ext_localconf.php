<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] = \Undkonsorten\MetaTag\Hooks\PageRendererHook::class . '->transferMetaTagsToPageRenderer';
});
