<?php

declare(strict_types=1);

namespace BabelForge\BabelChromeJsonViewerModule\Service;

use BabelForge\BabelChromeJsonViewerModule\Exception\JsonRenderException;
use BabelForge\BabelChromeJsonViewerModule\View\JsonView;
use BabelForge\BabelChromeViewerKit\OpenWithViewFactory;
use BabelForge\BabelChromeViewerKit\ViewerSource;
use Symfony\Component\HttpFoundation\Request;

/**
 * Renders JSON documents with the andypf/json-viewer web component.
 */
final readonly class JsonDocumentRenderer
{
    /**
     * Creates the JSON renderer.
     *
     * @param ModuleAssetResolver $assetPathResolver resolves module asset paths
     */
    public function __construct(
        private ModuleAssetResolver $assetPathResolver,
    ) {
    }

    /**
     * Renders a JSON source as a JSON viewer page model.
     *
     * @param ViewerSource $source  the document source
     * @param Request      $request the current request
     *
     * @return JsonView the rendered JSON view data
     *
     * @throws JsonRenderException when the JSON document cannot be parsed
     */
    public function render(ViewerSource $source, Request $request): JsonView
    {
        $documentJson = $this->documentJson($source);
        $lastModified = $source->lastModified;
        $sourceId = $this->sourceId($request);
        $openWithViewFactory = new OpenWithViewFactory();

        return new JsonView(
            $source->title,
            $openWithViewFactory->create($sourceId, $source->value, $source->local),
            $documentJson,
            $this->stylesheetContent()."\n".$this->styleContent('babel-chrome-viewer-kit/viewer-shell.css'),
            $this->vendorScriptContent(),
            $this->scriptContent('app/json.ts')."\n".$this->isolatedScriptContent('babel-chrome-viewer-kit/open-with.ts'),
            $sourceId,
            $source->local && null !== $lastModified && '' !== $sourceId,
            $lastModified,
        );
    }

    /**
     * Returns the last modification timestamp for a JSON source.
     *
     * @param ViewerSource $source the document source
     *
     * @return int|null the latest known local modification timestamp
     */
    public function sourceLastModified(ViewerSource $source): ?int
    {
        return $source->lastModified;
    }

    /**
     * Returns the current registered source identifier.
     *
     * @param Request $request the current request
     *
     * @return string the source identifier
     */
    private function sourceId(Request $request): string
    {
        $sourceIdValue = $request->attributes->get('sourceId', '');

        return is_string($sourceIdValue) ? $sourceIdValue : '';
    }

    /**
     * Returns the source document encoded as JSON for the browser viewer.
     *
     * @param ViewerSource $source the document source
     *
     * @return string the encoded JSON document
     *
     * @throws JsonRenderException when the JSON document cannot be parsed
     */
    private function documentJson(ViewerSource $source): string
    {
        $trimmedContent = trim($source->content);
        if ('' === $trimmedContent) {
            throw new JsonRenderException('The JSON document is empty.');
        }

        try {
            $decoded = json_decode($trimmedContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new JsonRenderException($exception->getMessage(), previous: $exception);
        }

        $encoded = json_encode(
            $decoded,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
        );

        if (false === $encoded) {
            throw new JsonRenderException('The JSON document could not be encoded for display.');
        }

        return $encoded;
    }

    /**
     * Returns the combined inline stylesheet content.
     *
     * @return string the safe inline stylesheet content
     */
    private function stylesheetContent(): string
    {
        return $this->styleContent('styles/viewer.css');
    }

    /**
     * Returns inline stylesheet content.
     *
     * @param string $logicalPath the module asset logical path
     *
     * @return string the safe inline stylesheet content
     */
    private function styleContent(string $logicalPath): string
    {
        return str_replace('</style', '<\/style', $this->assetPathResolver->content($logicalPath));
    }

    /**
     * Returns the local andypf/json-viewer IIFE bundle content.
     *
     * @return string the safe inline vendor script content
     */
    private function vendorScriptContent(): string
    {
        return $this->scriptContent('vendor/andypf-json-viewer/index.js');
    }

    /**
     * Returns inline script content.
     *
     * @param string $logicalPath the module asset logical path
     *
     * @return string the safe inline script content
     */
    private function scriptContent(string $logicalPath): string
    {
        return str_replace('</script', '<\/script', $this->assetPathResolver->content($logicalPath));
    }

    /**
     * Returns inline script content isolated from other compiled bundles.
     *
     * @param string $logicalPath the module asset logical path
     *
     * @return string the isolated safe inline script content
     */
    private function isolatedScriptContent(string $logicalPath): string
    {
        return "(function () {\n".$this->scriptContent($logicalPath)."\n})();";
    }
}
