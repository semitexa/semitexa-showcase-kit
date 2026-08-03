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
 * that is genuinely showcase-kit's: the header with the file label and language
 * badge.
 */
#[AsService]
final class CodeHighlighter
{
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
     * A complete code block: optional labelled header, then the highlighted body.
     *
     * The header is emitted only when a label exists — an unlabelled snippet gets
     * no empty chrome. The language badge rides inside the header, so it cannot
     * appear on its own.
     */
    public function codeBlock(string $code, ?string $lang = null, ?string $label = null): string
    {
        return '<div class="code-block">' . $this->header($lang, $label)
            . '<pre class="code-block__pre"><code class="code-block__code">'
            . (string) $this->highlightPhp($code)
            . '</code></pre></div>';
    }

    private function header(?string $lang, ?string $label): string
    {
        if ($label === null || $label === '') {
            return '';
        }

        $header = '<div class="code-block__header"><div class="code-block__file">'
            . '<span class="code-block__label">' . self::escape($label) . '</span>';

        if ($lang !== null && $lang !== '') {
            $header .= '<span class="code-block__file-note">' . self::escape($lang) . '</span>';
        }

        return $header . '</div></div>';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
