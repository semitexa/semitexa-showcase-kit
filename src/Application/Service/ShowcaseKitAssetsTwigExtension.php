<?php

declare(strict_types=1);

namespace App\Modules\ShowcaseKit\Application\Service;

use Semitexa\Ssr\Application\Service\Asset\AssetCollectorStore;
use Semitexa\Ssr\Application\Service\Extension\TwigExtensionRegistry;
use Semitexa\Ssr\Attribute\AsTwigExtension;

/**
 * Adds a `require_module('<Module>')` Twig function so a template can pull in
 * another module's asset manifest (assets.json css/js) through the standard
 * AssetCollector — emitted by the existing `asset_head()` / `asset_body()`.
 *
 * The kit layouts extend a layout from another module's namespace, so the
 * framework's namespace-based auto-require does NOT pick up the kit's own
 * assets. Calling `require_module('ShowcaseKit')` in the kit base layout makes
 * the kit's CSS/JS load natively (no hand-maintained <link>/<script> tags).
 */
#[AsTwigExtension]
final class ShowcaseKitAssetsTwigExtension
{
    public function registerFunctions(): void
    {
        TwigExtensionRegistry::registerFunction(
            'require_module',
            [$this, 'requireModule'],
            ['is_safe' => ['html']],
        );
    }

    public function requireModule(string $module): string
    {
        if (class_exists(AssetCollectorStore::class)) {
            AssetCollectorStore::get()->requireModule($module);
        }

        return '';
    }
}
