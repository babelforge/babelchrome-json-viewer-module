type JsonViewerElement = HTMLElement & {
  data?: unknown;
};

const sourceElement = document.querySelector<HTMLScriptElement>('#json-source');
const viewerElement = document.querySelector<HTMLElement>('[data-json-viewer]');
const jsonViewerElement = document.querySelector<JsonViewerElement>('#json-viewer-root');

if (null !== sourceElement && null !== jsonViewerElement) {
  jsonViewerElement.data = JSON.parse(sourceElement.textContent ?? 'null') as unknown;
}

const sourceId = viewerElement?.dataset.sourceId ?? '';
const autoRefreshEnabled = '1' === (viewerElement?.dataset.autoRefresh ?? '');
const initialLastModified = Number.parseInt(viewerElement?.dataset.lastModified ?? '', 10);

if (autoRefreshEnabled && '' !== sourceId && Number.isFinite(initialLastModified)) {
  let knownLastModified = initialLastModified;
  window.setInterval(() => {
    void (async () => {
      try {
        const query = new URLSearchParams(window.location.search);
        query.set('viewer', 'json');
        const response = await fetch(`/source-status/${encodeURIComponent(sourceId)}?${query.toString()}`, {
          cache: 'no-store',
        });
        if (!response.ok) {
          return;
        }

        const payload = (await response.json()) as { lastModified?: number };
        if ('number' === typeof payload.lastModified && payload.lastModified > knownLastModified) {
          knownLastModified = payload.lastModified;
          window.location.reload();
        }
      } catch {
        // Auto-refresh is best-effort and must never disturb reading.
      }
    })();
  }, 1600);
}
