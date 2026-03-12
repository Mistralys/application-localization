# Plan

## Summary

The `TranslationExporter` and `TranslationImporter` tools today work well for the
library's own development workflow (`composer export-translations` /
`composer import-translations`), but consuming applications cannot use them correctly
because (1) the CLI entry-point default config-path fallback resolves to the library
package root instead of the consumer's project root, and (2) the facade exposes no
factory methods for the tools, making programmatic use unnecessarily verbose and
invisible. This plan fixes both issues and documents the integration contract for
consuming applications.

---

## Architectural Context

### Tools layer (`AppLocalize\Tools`)

| File | Role |
|---|---|
| `src/Tools/TranslationExporter.php` | Reads `storage.json` + INI files, writes per-locale JSON export files |
| `src/Tools/TranslationImporter.php` | Reads per-locale JSON export files, writes INI files via `LocalizationWriter` |

Both classes expose two access paths:

| Path | Entry Point | Who is the caller? |
|---|---|---|
| **CLI** | `static run()` | Composer script runner (`composer export-translations`) |
| **Programmatic** | `static create()` → instance `export()` / `import()` | Application PHP code |

### Current CLI default config-path resolution (`loadConfig()`)

Both classes share identical `loadConfig()` logic with this fallback order:

1. PHP constant `LOCALIZATION_TOOLS_CONFIG`
2. Environment variable `LOCALIZATION_TOOLS_CONFIG`
3. **Default: `__DIR__ . '/../../localization-tools-config.php'`**

Step 3 resolves to `vendor/mistralys/application-localization/localization-tools-config.php`
when the library is installed as a dependency — the library's own config file inside
`vendor/`, not the consuming app's project root. This means a consuming app cannot 
rely on the default-path fallback to load their own configuration.

### Localization facade (`src/Localization.php`)

The facade follows a consistent `create*()` factory pattern for all subsystems:
`createScanner()`, `createEditor()`, `createGenerator()`, `createCountries()`,
`createCurrencies()`. No such factory exists for `TranslationExporter` or
`TranslationImporter`, making them hard to discover and requiring consumers to import
`AppLocalize\Tools\*` classes directly.

### Existing tests

`tests/testsuites/Tools/` already contains `TranslationExporterTest.php`,
`TranslationImporterTest.php`, and `RoundTripTest.php`.

---

## Approach / Architecture

Two targeted, non-breaking changes are needed:

### Change 1 — Fix the CLI default config-path fallback

Replace `__DIR__ . '/../../'` with `getcwd()` in both `loadConfig()` methods.  
When Composer scripts execute, `getcwd()` returns the **project root of the application
running Composer**, which is precisely where a consumer would place their
`localization-tools-config.php`. The library's own `localization-tools-config.php` is
already found via the `LOCALIZATION_TOOLS_CONFIG` constant it defines internally, so
the library's self-tests are unaffected.

> **Backwards compatible:** changes 1 & 2 (constant/env-var) are untouched.
> The fallback path simply becomes more general.

### Change 2 — Expose `createExporter()` and `createImporter()` on the facade

Add two `public static` factory methods to `src/Localization.php` that mirror the
existing `create*()` pattern:

```php
public static function createExporter() : TranslationExporter;
public static function createImporter() : TranslationImporter;
```

These are thin wrappers around the existing `TranslationExporter::create()` and
`TranslationImporter::create()` statics, giving consuming apps a single, consistent,
discoverable entry point through the facade.

### Consuming-application integration contract (documentation only)

The combination of these two changes unlocks two integration modes for consumers:

**A. CLI mode (Composer scripts)**  
Add to the app's `composer.json`:
```json
"scripts": {
    "export-translations": "\\AppLocalize\\Tools\\TranslationExporter::run",
    "import-translations": "\\AppLocalize\\Tools\\TranslationImporter::run"
}
```
Create `localization-tools-config.php` at the project root (mirrors the shape of
`localization-tools-config.php` in this package). No constant or env-var needed.

**B. Programmatic mode**  
```php
Localization::addAppLocale('de_DE');
Localization::configure($storageFile);

Localization::createExporter()->export();
Localization::createImporter()->import();
```

---

## Rationale

- **`getcwd()` over a traversal**: Composer always sets `cwd` to the project root when
  invoking class-callable scripts. It is reliable, simpler than traversing up the
  directory tree to find `composer.json`, and consistent with how other Composer-aware
  tools (e.g., PHPStan) locate config.
- **Facade factory methods**: Matching the existing `create*()` pattern requires zero
  new infrastructure and makes the tools first-class citizens alongside the scanner and
  editor.
- **No new classes**: Both deliverables are small, targeted edits to existing files,
  keeping the footprint minimal.

---

## Detailed Steps

1. **Fix `TranslationExporter::loadConfig()`** (`src/Tools/TranslationExporter.php`)  
   In the default-fallback branch (step 3), replace:
   ```php
   $defaultPath = __DIR__ . '/../../localization-tools-config.php';
   ```
   with:
   ```php
   $defaultPath = getcwd() . '/localization-tools-config.php';
   ```

2. **Fix `TranslationImporter::loadConfig()`** (`src/Tools/TranslationImporter.php`)  
   Same single-line replacement as step 1.

3. **Add `Localization::createExporter()`** (`src/Localization.php`)  
   Add a `use AppLocalize\Tools\TranslationExporter;` import and a new factory method
   immediately after `createScanner()` (or in the factory-methods region), returning
   `TranslationExporter::create()`.

4. **Add `Localization::createImporter()`** (`src/Localization.php`)  
   Add a `use AppLocalize\Tools\TranslationImporter;` import and a new factory method
   after `createExporter()`, returning `TranslationImporter::create()`.

5. **Update manifest — `api-core.md`**  
   Add the two new facade methods under "Factory Methods":
   ```
   public static function createExporter() : TranslationExporter;
   public static function createImporter() : TranslationImporter;
   ```

6. **Update manifest — `api-translator-editor.md`**  
   In the "Config Resolution Order" section, clarify that the default fallback uses
   `getcwd()` so that apps placing `localization-tools-config.php` at their project root
   are picked up automatically when running Composer scripts.

7. **Add tests for the new facade methods** (`tests/testsuites/Tools/`)  
   Add assertions to the existing tool test files (or create a small
   `LocalizationFacadeToolsTest.php`) verifying that:
   - `Localization::createExporter()` returns a `TranslationExporter` instance.
   - `Localization::createImporter()` returns a `TranslationImporter` instance.

8. **Run static analysis**  
   Execute `composer analyze` and resolve any PHPStan level-8 issues introduced by the
   new `use` imports and method signatures.

9. **Run the full test suite**  
   Execute `composer test` to confirm no regressions.

---

## Dependencies

- No new Composer packages needed.
- Steps 3–4 depend on the `TranslationExporter` and `TranslationImporter` classes
  remaining in `AppLocalize\Tools` (no namespace change).

---

## Required Components

| Component | Status | Location |
|---|---|---|
| `src/Tools/TranslationExporter.php` | Modify — fix `loadConfig()` fallback | Existing |
| `src/Tools/TranslationImporter.php` | Modify — fix `loadConfig()` fallback | Existing |
| `src/Localization.php` | Modify — add two factory methods + `use` imports | Existing |
| `docs/agents/project-manifest/api-core.md` | Modify — document two new facade methods | Existing |
| `docs/agents/project-manifest/api-translator-editor.md` | Modify — update config resolution note | Existing |
| `tests/testsuites/Tools/LocalizationFacadeToolsTest.php` | New — factory method smoke tests | New |

---

## Assumptions

- Consumers use standard Composer project layout (`composer.json` at project root,
  `vendor/` inside).
- `getcwd()` equals the Composer project root when Composer class-callable scripts run.
  This is true for `composer run` and inline `scripts` invocations but not guaranteed
  if a consumer builds a custom PHP CLI that changes directory before calling `run()`.
  For that edge case, the existing `LOCALIZATION_TOOLS_CONFIG` constant/env-var escape
  hatch remains available.
- The library's self-wiring (`localization-tools-config.php` at the library root)
  continues to work because `post-autoload-dump` is only meaningful when the library is
  the Composer root (i.e., during library development).

---

## Constraints

- PHPStan level 8 must continue to pass (`phpstan.neon`).
- The `TranslationExporter` and `TranslationImporter` classes remain in
  `AppLocalize\Tools` — no namespace or class-name changes.
- The existing `run()` constant-and-env-var resolution order (steps 1 & 2) must not
  change.
- `CannedCountries`, `CannedCurrencies`, `CannedLocales` are untouched.

---

## Out of Scope

- Changing the JSON export format (`format_version`).
- Adding a domain-specific CLI binary (e.g., a `bin/` script) — the Composer-scripts
  approach is sufficient.
- Supporting multi-root Composer workspaces.
- Adding import/export progress events or callbacks.

---

## Acceptance Criteria

- [ ] Running `composer export-translations` from a consuming app's root with a
      `localization-tools-config.php` at that root (and no constant/env-var set) completes
      successfully without requiring any `LOCALIZATION_TOOLS_CONFIG` override.
- [ ] Running `composer import-translations` under the same conditions succeeds.
- [ ] `Localization::createExporter()` is callable and returns a `TranslationExporter`
      instance after `Localization` has been configured.
- [ ] `Localization::createImporter()` is callable and returns a `TranslationImporter`
      instance after `Localization` has been configured.
- [ ] The library's own `composer export-translations` and `composer import-translations`
      (which set `LOCALIZATION_TOOLS_CONFIG` via the constant defined in
      `localization-tools-config.php`) remain unaffected.
- [ ] PHPStan level 8 passes cleanly.
- [ ] All existing tests pass and the new smoke tests pass.

---

## Testing Strategy

- **Unit/integration tests** for the two new facade methods: instantiate the façade in
  test configuration, call `createExporter()` / `createImporter()`, assert instance type.
- **Regression** using the existing `TranslationExporterTest.php`,
  `TranslationImporterTest.php`, and `RoundTripTest.php` which exercise the programmatic
  `create()` / `export()` / `import()` path configured with real test fixtures in
  `tests/storage/`.
- **Manual verification** of the CLI path: confirming `getcwd()` returns the correct root
  in the context of Composer script invocation (can be observed via the existing
  `composer export-translations` on the library itself, which now has `getcwd()` ===
  library root, consistent with the former `__DIR__ . '/../../'` resolution).

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`getcwd()` differs from project root** in exotic CI environments | Steps 1 & 2 (constant + env-var) remain as overrides; document the escape hatch clearly. |
| **Naming collision**: a consumer names a method `createExporter()` on a subclass | The facade is `final`-intent static; the method names are new and not previously used. No inheritance risk. |
| **Circular dependency**: `Localization.php` importing from `AppLocalize\Tools\*`** | Both `TranslationExporter` and `TranslationImporter` already import `Localization` (one-way). Adding reverse imports creates a cycle. **Mitigation**: The factory methods must call `TranslationExporter::create()` / `TranslationImporter::create()` and return the result — not reference the class type in the body in any way that triggers autoload at `Localization::init()` time. Using `use` import statements plus a simple method call is fine in PHP because the class body is parsed lazily on first call, not at include time. Static analysis (`@return` PHPDoc) will use the imported types, which is standard practice in this codebase already (e.g. `createEditor()` imports `LocalizationEditor`). PHPStan will validate this. |
