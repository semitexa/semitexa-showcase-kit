<?php

declare(strict_types=1);

namespace App\Modules\ShowcaseKit;

use Semitexa\Core\Attribute\Capability;

/**
 * What this package offers, for the capability catalog.
 *
 * The package ships no attributes of its own, so there is nothing for a
 * mechanism-level declaration to hang on — and without this the package is
 * invisible to anyone whose project has not installed it, which is precisely
 * the audience worth telling. The convention is one `Capabilities` class per
 * package: a definite place to look, and a definite place for a guard to check.
 *
 * Nothing reads this at runtime.
 */
#[Capability(
    id: 'site.showcase-kit',
    summary: 'The shared structure behind a docs-backed feature site: layout, feature tree, and L1/L2/L3 feature pages driven by content.',
    useWhen: 'Building a second marketing or documentation site that differs from the first only in palette and content.',
    avoidWhen: 'A one-off site with its own information architecture. The kit is a shape, and fighting the shape costs more than writing the pages.',
    replaces: [
        'a copy of the first site with its palette and text edited in place',
        'a hand-maintained navigation tree that has to be touched every time a page is added',
    ],
)]
final class Capabilities
{
}
