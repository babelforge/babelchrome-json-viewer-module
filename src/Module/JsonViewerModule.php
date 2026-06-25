<?php

declare(strict_types=1);

namespace BabelForge\BabelChromeJsonViewerModule\Module;

use BabelForge\BabelChrome\LocalViewer\Module\BabelChromeModuleInterface;
use BabelForge\BabelChrome\LocalViewer\Module\ModuleRequest;
use BabelForge\BabelChromeJsonViewerModule\Controller\JsonViewerController;
use BabelForge\BabelChromeJsonViewerModule\Service\ViewerModuleSupport;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders JSON documents as a BabelChrome viewer module.
 */
final class JsonViewerModule extends ViewerModuleSupport implements BabelChromeModuleInterface
{
    /**
     * Handles one JSON viewer module request.
     *
     * @param ModuleRequest $request the module request context
     *
     * @return Response the rendered JSON response
     */
    public function handle(ModuleRequest $request): Response
    {
        $sourceRegistry = $this->sourceRegistry();

        return new JsonViewerController(
            $this->twig(),
            $this->sourceLoader($sourceRegistry),
            $this->assetPathResolver($request->module, $request->context),
        )->render($request->request);
    }
}
