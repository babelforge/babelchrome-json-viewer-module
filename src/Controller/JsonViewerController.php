<?php

declare(strict_types=1);

namespace BabelForge\BabelChromeJsonViewerModule\Controller;

use BabelForge\BabelChrome\LocalViewer\DocumentSource;
use BabelForge\BabelChrome\LocalViewer\Service\SourceLoader;
use BabelForge\BabelChromeJsonViewerModule\Exception\JsonRenderException;
use BabelForge\BabelChromeJsonViewerModule\Service\JsonDocumentRenderer;
use BabelForge\BabelChromeJsonViewerModule\Service\ModuleAssetResolver;
use BabelForge\BabelChromeJsonViewerModule\View\JsonView;
use BabelForge\BabelChromeViewerKit\Controller\AbstractViewerController;
use BabelForge\BabelChromeViewerKit\ViewerSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * Handles Symfony HTTP rendering for JSON viewer pages.
 */
final class JsonViewerController extends AbstractViewerController
{
    /**
     * @param Environment         $twig              renders viewer templates
     * @param SourceLoader        $sourceLoader      loads viewer sources
     * @param ModuleAssetResolver $assetPathResolver resolves module assets
     */
    public function __construct(
        Environment $twig,
        private readonly SourceLoader $sourceLoader,
        private readonly ModuleAssetResolver $assetPathResolver,
    ) {
        parent::__construct($twig);
    }

    /**
     * Renders a JSON document.
     *
     * @param Request $request the current request
     *
     * @return Response the rendered JSON response
     */
    #[Route('/render', name: 'babelforge_json_viewer_render', methods: ['GET'])]
    public function render(Request $request): Response
    {
        return parent::render($request);
    }

    /**
     * Loads the JSON source for the current request.
     *
     * @param Request $request the current request
     *
     * @return ViewerSource|null the loaded viewer source
     */
    protected function loadSource(Request $request): ?ViewerSource
    {
        $source = $this->sourceLoader->load($request);

        return null === $source ? null : $this->viewerSource($source);
    }

    /**
     * Renders the JSON-specific view model.
     *
     * @param ViewerSource $source  the loaded viewer source
     * @param Request      $request the current request
     *
     * @return JsonView the rendered JSON view model
     *
     * @throws JsonRenderException when the JSON document cannot be parsed
     */
    protected function renderView(ViewerSource $source, Request $request): JsonView
    {
        return new JsonDocumentRenderer($this->assetPathResolver)->render($source, $request);
    }

    /**
     * Returns the Twig template used by the JSON viewer.
     *
     * @return string the template name
     */
    protected function templateName(): string
    {
        return 'json/show.html.twig';
    }

    /**
     * Converts a rendering failure into a response when the viewer supports it.
     *
     * @param \Throwable $exception the rendering failure
     *
     * @return Response|null the failure response, or null to rethrow
     */
    protected function renderingFailureResponse(\Throwable $exception): ?Response
    {
        if (!$exception instanceof JsonRenderException) {
            return null;
        }

        return $this->errorResponse(
            'Unable to Render JSON',
            'JSON document is invalid',
            'The JSON document could not be parsed.',
            $exception->getMessage(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->errorStylesheetContent(),
        );
    }

    /**
     * Returns the source-not-found page title.
     *
     * @return string the page title
     */
    protected function sourceNotFoundTitle(): string
    {
        return 'Unable to Load JSON';
    }

    /**
     * Returns the source-not-found visible heading.
     *
     * @return string the visible heading
     */
    protected function sourceNotFoundHeading(): string
    {
        return 'JSON source not found';
    }

    /**
     * Returns the source-not-found visible message.
     *
     * @return string the visible message
     */
    protected function sourceNotFoundMessage(): string
    {
        return 'The JSON file or remote JSON document could not be loaded.';
    }

    /**
     * Returns the stylesheet content used by shared error pages.
     *
     * @return string the safe inline stylesheet content
     */
    protected function errorStylesheetContent(): string
    {
        return str_replace('</style', '<\/style', $this->assetPathResolver->content('styles/viewer.css'));
    }

    /**
     * Converts a host document source into a kit viewer source.
     *
     * @param DocumentSource $source the host document source
     *
     * @return ViewerSource the kit viewer source
     */
    private function viewerSource(DocumentSource $source): ViewerSource
    {
        return new ViewerSource(
            $source->title,
            $source->content,
            $source->baseUri,
            $source->local,
            $source->type,
            $source->value,
            $source->mimeType,
            $source->lastModified,
        );
    }
}
