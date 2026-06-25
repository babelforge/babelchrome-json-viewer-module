<?php

declare(strict_types=1);

namespace BabelForge\BabelChromeJsonViewerModule\View;

use BabelForge\BabelChromeViewerKit\OpenWithView;

/**
 * Carries all data needed to render a JSON viewer page.
 */
final readonly class JsonView
{
    /**
     * @param string       $title               the document title
     * @param OpenWithView $openWithView        the shared Open With control view model
     * @param string       $documentJson        the normalized JSON document
     * @param string       $stylesheetContent   the inline stylesheet content
     * @param string       $vendorScriptContent the inline andypf/json-viewer bundle content
     * @param string       $scriptContent       the inline module script content
     * @param string       $sourceId            the registered source identifier
     * @param bool         $autoRefreshEnabled  whether auto refresh is enabled
     * @param int|null     $lastModified        the source last modification timestamp
     */
    public function __construct(
        public string $title,
        public OpenWithView $openWithView,
        public string $documentJson,
        public string $stylesheetContent,
        public string $vendorScriptContent,
        public string $scriptContent,
        public string $sourceId,
        public bool $autoRefreshEnabled,
        public ?int $lastModified,
    ) {
    }
}
