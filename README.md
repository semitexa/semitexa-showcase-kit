# Semitexa Showcase Kit

`semitexa/showcase-kit`

The shared structural layer for docs-backed feature-showcase sites: layout, feature tree and L1/L2/L3 feature pages, where only the palette and content differ per site. `semitexa/demo` is built on it.

## Install

Not included by the installer. Add it to an existing project from the project root:

```bash
docker compose run --rm --no-deps --user "$(id -u):$(id -g)" app composer require semitexa/showcase-kit
bin/semitexa server:restart
```

You normally get it as a dependency of `semitexa/demo` (`bin/semitexa demo:install`) rather than on its own.

## What it provides

- Twig templates: layouts `showcase` and `feature`, pages `home` and `section`, partials `feature-tree`, `_feature-card`, `disclosure-prompt`.
- Twig functions: `sk_code_block()` and `sk_code_tabs()` (highlighted code), `require_module()` (pulls in a module's assets).
- CSS (`tokens`, `layout`, `nav`, `feature`, `code`) and small scripts (feature tree, disclosure, nav toggle, skin toggle, code block).

No console commands, routes or tables.

## License

MIT, see [LICENSE](LICENSE).
