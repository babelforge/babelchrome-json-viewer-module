<?php

declare(strict_types=1);

namespace BabelForge\BabelChromeJsonViewerModule\Tests;

use BabelForge\BabelChrome\LocalViewer\Module\ModuleManifest;
use BabelForge\BabelChrome\LocalViewer\Module\ModuleRequest;
use BabelForge\BabelChrome\LocalViewer\Module\ModuleRuntimeContext;
use BabelForge\BabelChromeJsonViewerModule\Module\JsonViewerModule;
use BabelForge\BabelChromeJsonViewerModule\Service\JsonDocumentRenderer;
use BabelForge\BabelChromeJsonViewerModule\Service\ModuleAssetResolver;
use BabelForge\BabelChromeViewerKit\ViewerSource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the JSON viewer module renderer.
 */
final class JsonDocumentRendererTest extends TestCase
{
    /**
     * Verifies that JSON rendering uses module-local runtime assets.
     */
    public function testRenderUsesModuleAssets(): void
    {
        $renderer = new JsonDocumentRenderer($this->assetResolver());
        $view = $renderer->render(
            new ViewerSource('data.json', '{"name":"BabelChrome","items":[1,true,null]}', '', false, 'file', '/tmp/data.json', 'application/json', null),
            Request::create('/json'),
        );

        self::assertSame('data.json', $view->title);
        self::assertStringContainsString('BabelChrome', $view->documentJson);
        self::assertStringContainsString('json-viewer-root', $view->stylesheetContent);
        self::assertStringContainsString('andypf-json-viewer', $view->vendorScriptContent);
        self::assertStringContainsString('json-source', $view->scriptContent);
    }

    /**
     * Verifies that the module renders JSON inside the web component.
     */
    public function testModuleRendersJsonInsideWebComponent(): void
    {
        $request = Request::create('/json', 'GET', [
            'content' => '{"name":"BabelChrome"}',
        ]);
        $response = new JsonViewerModule()->handle(new ModuleRequest(
            new ModuleManifest('babelforge.json-viewer', 'JSON Viewer', '1.0.0'),
            'json',
            $request,
            new ModuleRuntimeContext('http://127.0.0.1:12345', 'test-token', 'babelchrome://viewer/file/%2Ftmp%2Fdata.json'),
        ));

        $content = (string) $response->getContent();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('<andypf-json-viewer', $content);
        self::assertStringContainsString('{"name":"BabelChrome"}', $content);
        self::assertStringContainsString('</andypf-json-viewer>', $content);
        self::assertGreaterThan(
            strpos($content, '</andypf-json-viewer>'),
            strpos($content, 'querySelector("#json-source")'),
        );
    }

    /**
     * Verifies that invalid JSON is rejected with a clear exception.
     */
    public function testRenderRejectsInvalidJson(): void
    {
        $renderer = new JsonDocumentRenderer($this->assetResolver());

        $this->expectException(\RuntimeException::class);

        $renderer->render(
            new ViewerSource('broken.json', '{"name":', '', false, 'file', '/tmp/broken.json', 'application/json', null),
            Request::create('/json'),
        );
    }

    /**
     * Creates the module asset resolver used by tests.
     *
     * @return ModuleAssetResolver the module asset resolver
     */
    private function assetResolver(): ModuleAssetResolver
    {
        return new ModuleAssetResolver(
            new ModuleManifest('babelforge.json-viewer', 'JSON Viewer', '1.0.0'),
            new ModuleRuntimeContext('http://127.0.0.1:12345', 'test-token', 'babelchrome://json/test'),
        );
    }
}
