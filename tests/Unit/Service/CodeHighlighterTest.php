<?php

declare(strict_types=1);

namespace App\Modules\ShowcaseKit\Tests\Unit\Service;

use App\Modules\ShowcaseKit\Application\Service\CodeHighlighter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The chrome showcase-kit draws around a snippet: what a prose fence gets,
 * what an example block gets, and the ids the copy button and tabs rely on.
 */
final class CodeHighlighterTest extends TestCase
{
    private CodeHighlighter $highlighter;

    protected function setUp(): void
    {
        $this->highlighter = new CodeHighlighter();
    }

    #[Test]
    public function tabs_without_code_render_nothing(): void
    {
        self::assertSame('', $this->highlighter->codeTabs([['code' => "  \n"], ['label' => 'Empty']]));
        self::assertSame('', $this->highlighter->codeBlock(''));
    }

    #[Test]
    public function a_prose_fence_has_no_masthead_no_tab_strip_and_no_invented_note(): void
    {
        $html = $this->highlighter->codeBlock("echo 1;\n", null, 'Handler');

        self::assertStringNotContainsString('code-block__masthead', $html);
        self::assertStringNotContainsString('role="tablist"', $html);
        // "Handler" would map to a layer in an example block; a fence names none.
        self::assertStringNotContainsString('code-block__file-note', $html);
        self::assertStringContainsString('<span class="code-block__label">Handler</span>', $html);
    }

    #[Test]
    public function an_example_block_has_a_masthead_tabs_and_layer_notes(): void
    {
        $html = $this->highlighter->codeTabs([
            ['code' => 'final class ShowHandler {}', 'label' => 'Handler'],
            ['code' => '{{ title }}', 'label' => 'Template', 'lang' => 'twig'],
        ], 'feature');

        self::assertStringContainsString('code-block__masthead', $html);
        self::assertStringContainsString('role="tablist"', $html);
        self::assertStringContainsString('Application entry point', $html);
        // An explicit language wins over the layer map.
        self::assertStringContainsString('<span class="code-block__tab-note">twig</span>', $html);
        self::assertStringNotContainsString('Rendered HTML surface', $html);
        // Only the first panel is visible.
        self::assertSame(1, substr_count($html, 'code-block__panel--active'));
        self::assertSame(1, substr_count($html, 'role="tabpanel" hidden'));
    }

    #[Test]
    public function ids_are_unique_per_content_and_stable_across_renders(): void
    {
        $first = $this->panelId($this->highlighter->codeBlock('echo 1;', 'php', 'php'));
        $second = $this->panelId($this->highlighter->codeBlock('echo 2;', 'php', 'php'));

        // Every php fence on a page arrives with the same label.
        self::assertNotSame($first, $second);
        self::assertSame($first, $this->panelId($this->highlighter->codeBlock('echo 1;', 'php', 'php')));
    }

    #[Test]
    public function the_copy_source_is_the_raw_text_escaped_without_the_trailing_newline(): void
    {
        $html = $this->highlighter->codeBlock("<?php echo '<b>';\n");

        self::assertSame(1, preg_match('#<textarea id="(code-raw-[^"]+)"[^>]*>(.*?)</textarea>#s', $html, $m));
        self::assertSame('&lt;?php echo &#039;&lt;b&gt;&#039;;', $m[2]);
        self::assertStringContainsString('data-copy-raw-source="' . $m[1] . '"', $html);
    }

    private function panelId(string $html): string
    {
        self::assertSame(1, preg_match('#<div id="(code-panel-[^"]+)"#', $html, $m));

        return $m[1];
    }
}
