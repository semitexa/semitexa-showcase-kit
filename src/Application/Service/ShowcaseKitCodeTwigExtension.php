<?php

declare(strict_types=1);

namespace App\Modules\ShowcaseKit\Application\Service;

use Semitexa\Ssr\Application\Service\Extension\TwigExtensionRegistry;
use Semitexa\Ssr\Attribute\AsTwigExtension;

/**
 * Exposes `sk_code_block(code, lang?, label?)` — renders a highlighted
 * `.code-block` (styled by ShowcaseKit code.css). Separate from the demo's
 * `highlight_*` functions to avoid global-name clashes.
 */
#[AsTwigExtension]
final class ShowcaseKitCodeTwigExtension
{
    public function registerFunctions(): void
    {
        TwigExtensionRegistry::registerFunction(
            'sk_code_block',
            [$this, 'codeBlock'],
            ['is_safe' => ['html']],
        );
    }

    public function codeBlock(mixed $code, ?string $lang = null, ?string $label = null): string
    {
        return (new CodeHighlighter())->codeBlock((string) $code, $lang, $label);
    }
}
