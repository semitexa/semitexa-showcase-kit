<?php

declare(strict_types=1);

namespace App\Modules\ShowcaseKit\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Ssr\Application\Service\Code\CodeHighlighter as SharedHighlighter;
use Twig\Markup;

/**
 * Showcase-kit's code presentation: the shared highlighter, wrapped in the
 * chrome the showcase site renders around a snippet.
 *
 * The highlighting itself moved to {@see SharedHighlighter} in semitexa-ssr by
 * ep-duplication-sweep. This class had carried its own copy, byte-identical to
 * the one in semitexa-demo down to 183 statements of tokenizer — so a fix to
 * either would have silently missed the other. What remains here is the part
 * that is genuinely showcase-kit's: the chrome around a snippet.
 *
 * That chrome mirrors the framework demo's code-block component: a masthead
 * carrying a programming quote, a tab strip when a feature has more than one
 * source, a copy button per panel, and a line gutter. The showcase is meant to
 * be the demo re-skinned and re-contented — same structure, different palette —
 * and for a while it was not: this class rendered a header and a <pre>, and
 * nothing else.
 *
 * Prose fences are the one deliberate departure. {@see self::codeBlock()} skips
 * the masthead, because a page of documentation renders many fences and the demo
 * gives a feature exactly one quote, not one per snippet.
 */
#[AsService]
final class CodeHighlighter
{
    /**
     * Masthead quotes, one picked at random per rendered block. Same set the
     * framework demo ships — the showcase mirrors the demo's structure and
     * content here, only the palette differs.
     *
     * @var list<array{quote:string,author:string}>
     */
    private const CODE_QUOTES = [
        ['quote' => 'Talk is cheap. Show me the code.', 'author' => 'Linus Torvalds'],
        ['quote' => 'Programs must be written for people to read, and only incidentally for machines to execute.', 'author' => 'Harold Abelson'],
        ['quote' => 'Simplicity is prerequisite for reliability.', 'author' => 'Edsger W. Dijkstra'],
        ['quote' => 'Controlling complexity is the essence of computer programming.', 'author' => 'Brian Kernighan'],
        ['quote' => 'Deleted code is debugged code.', 'author' => 'Jeff Sickel'],
    ];

    /**
     * Label substring → the architectural layer the tab represents. Keyed the
     * same way as the demo's `annotationMap`; first match wins.
     *
     * @var array<string, string>
     */
    private const LAYER_ANNOTATIONS = [
        'payload' => 'Trusted input boundary',
        'handler' => 'Application entry point',
        'resource' => 'Presentation boundary',
        'template' => 'Rendered HTML surface',
        'slot' => 'Secondary page region',
        'repository' => 'Persistence adapter',
        'domain' => 'Business model',
        'schema' => 'Contract surface',
        'console' => 'CLI entry point',
    ];
    public function highlightPhp(mixed $source, int $mixedDepth = 0): Markup
    {
        return (new SharedHighlighter())->highlightPhp($source, $mixedDepth);
    }

    public function highlightSnippet(mixed $source): Markup
    {
        return (new SharedHighlighter())->highlightSnippet($source);
    }

    public function highlightPhpLines(mixed $source): Markup
    {
        return (new SharedHighlighter())->highlightPhpLines($source);
    }

    /**
     * A single snippet: same panel shape (copy button, line numbers) but no
     * quote masthead. This is what documentation fences render as — the demo
     * reserves the masthead for a feature's one example block, and repeating
     * the flourish once per prose fence would drown the page in quotes.
     */
    public function codeBlock(string $code, ?string $lang = null, ?string $label = null): string
    {
        return $this->codeTabs(
            [['code' => $code, 'lang' => $lang, 'label' => $label ?? 'Source']],
            $label ?? 'source',
            withMasthead: false,
        );
    }

    /**
     * Full `.code-block` markup mirroring the framework demo's component
     * (`semitexa-demo` components/code-block.html.twig): quote masthead, a tab
     * strip when there is more than one source, one panel per source with a
     * copy button, and line-numbered highlighted code. Structure is deliberately
     * identical to the demo — only the palette differs, via ShowcaseKit tokens.
     *
     * @param list<array{code?:mixed,lang?:?string,note?:?string,label?:?string,title?:?string}> $tabs
     */
    public function codeTabs(array $tabs, string $domKey = 'default', bool $withMasthead = true): string
    {
        $tabs = array_values(array_filter(
            $tabs,
            static fn (array $tab): bool => trim((string) ($tab['code'] ?? '')) !== '',
        ));

        if ($tabs === []) {
            return '';
        }

        $key = $this->domKeyFor($domKey, $tabs);

        $html = '<div class="code-block" data-code-block><div class="code-block__frame">';

        if ($withMasthead) {
            $quote = self::CODE_QUOTES[array_rand(self::CODE_QUOTES)];
            $html .= '<div class="code-block__masthead"><div class="code-block__intro">'
                . '<p class="code-block__quote-line">'
                . '<span class="code-block__quote-author">&copy; ' . self::escape($quote['author']) . ':</span>'
                . '<span class="code-block__headline">"' . self::escape($quote['quote']) . '"</span>'
                . '</p></div></div>';
        }

        if (count($tabs) > 1) {
            $html .= '<div class="code-block__tabs" role="tablist">';
            foreach ($tabs as $index => $tab) {
                $label = $this->tabLabel($tab);
                $html .= sprintf(
                    '<button class="code-block__tab%s" type="button" role="tab" aria-selected="%s"'
                        . ' aria-controls="code-panel-%s-%d" data-code-tab="%d">'
                        . '<span class="code-block__tab-title">%s</span>'
                        . '<span class="code-block__tab-note">%s</span></button>',
                    $index === 0 ? ' code-block__tab--active' : '',
                    $index === 0 ? 'true' : 'false',
                    $key,
                    $index + 1,
                    $index + 1,
                    self::escape($label),
                    self::escape($this->tabNote($tab, $label, $withMasthead)),
                );
            }
            $html .= '</div>';
        }

        foreach ($tabs as $index => $tab) {
            $label = $this->tabLabel($tab);
            // Markdown fences arrive with a trailing newline; left alone it
            // renders as a numbered empty last line under every snippet.
            $source = rtrim($this->normalizeSource($tab['code'] ?? ''));
            $note = $this->tabNote($tab, $label, $withMasthead);
            $position = $index + 1;

            $html .= sprintf(
                '<div id="code-panel-%s-%d" class="code-block__panel%s" role="tabpanel"%s>',
                $key,
                $position,
                $index === 0 ? ' code-block__panel--active' : '',
                $index === 0 ? '' : ' hidden',
            );
            $html .= '<div class="code-block__header"><div class="code-block__file">'
                . '<span class="code-block__label">' . self::escape($label) . '</span>'
                . ($note === '' ? '' : '<span class="code-block__file-note">' . self::escape($note) . '</span>')
                . '</div>'
                . sprintf(
                    '<button class="code-block__copy" type="button" title="Copy to clipboard"'
                        . ' data-copy-source="code-source-%s-%d" data-copy-raw-source="code-raw-%s-%d">Copy</button>',
                    $key,
                    $position,
                    $key,
                    $position,
                )
                . '</div>';
            $html .= sprintf(
                '<pre class="code-block__pre"><code id="code-source-%s-%d" class="code-block__code">%s</code></pre>',
                $key,
                $position,
                (string) $this->highlightPhpLines($source),
            );
            // Copy reads the raw source from here, not from the highlighted
            // markup — otherwise line numbers land in the clipboard.
            $html .= sprintf(
                '<textarea id="code-raw-%s-%d" class="code-block__raw-source" tabindex="-1" aria-hidden="true">%s</textarea>',
                $key,
                $position,
                self::escape($source),
            );
            $html .= '</div>';
        }

        return $html . '</div></div>';
    }

    /**
     * @param array{label?:?string,title?:?string} $tab
     */
    private function tabLabel(array $tab): string
    {
        foreach (['label', 'title'] as $candidate) {
            $value = trim((string) ($tab[$candidate] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return 'Source';
    }

    /**
     * An explicit note or language wins. Failing that, only an example block
     * ($annotate) falls back to the layer map: its tabs are slices of one
     * example, so naming the layer is the point. A prose fence has no layer to
     * name — labelling it "Implementation slice" would be noise — so it gets no
     * note at all.
     *
     * @param array{note?:?string,lang?:?string} $tab
     */
    private function tabNote(array $tab, string $label, bool $annotate): string
    {
        foreach (['note', 'lang'] as $candidate) {
            $value = trim((string) ($tab[$candidate] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        if (!$annotate) {
            return '';
        }

        $lowerLabel = mb_strtolower($label);
        foreach (self::LAYER_ANNOTATIONS as $needle => $annotation) {
            if (str_contains($lowerLabel, $needle)) {
                return $annotation;
            }
        }

        return 'Implementation slice';
    }

    /**
     * DOM-id stem for one block. The caller's key alone is not unique — every
     * `php` fence in a document arrives with the same label — so a short digest
     * of the sources is mixed in. Content-derived rather than a counter on
     * purpose: a Swoole worker is long-lived, and per-request state must never
     * live in a static property.
     *
     * @param list<array{code?:mixed}> $tabs
     */
    private function domKeyFor(string $domKey, array $tabs): string
    {
        $key = strtolower((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $domKey));
        $key = trim($key, '-');

        $digest = substr(hash('sha256', implode("\0", array_map(
            static fn (array $tab): string => (string) ($tab['code'] ?? ''),
            $tabs,
        ))), 0, 8);

        return ($key === '' ? 'code' : $key) . '-' . $digest;
    }

    /**
     * The raw text behind a snippet. {@see SharedHighlighter} normalises its own
     * input privately; the copy button needs the same text un-highlighted, so
     * this class keeps a minimal copy rather than reaching into it.
     */
    private function normalizeSource(mixed $source): string
    {
        if ($source instanceof Markup || is_scalar($source) || $source instanceof \Stringable) {
            return (string) $source;
        }

        return '';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
