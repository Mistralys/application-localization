# Plan

## Summary

Add `composer export-translations` and `composer import-translations` commands that produce
and consume per-locale-per-source JSON files. Each export file contains the original English
text, the translator context/explanation, the file locations where the string appears, and the
current translated value. The files act as a human- and machine-readable bridge between the
`storage.json` scan results and the compiled INI translation files, making it trivial to send
strings to a human or LLM translator and fold the results back into the authoritative INI files.

---

## Architectural Context

### Existing translation subsystem relevant to this change

| Component | Role | Location |
|-----------|------|----------|
| `LocalizationScanner` (`Scanner/LocalizationScanner.php`) | Loads/saves `storage.json`; owns the `StringCollection` | `src/Localization/Scanner/` |
| `StringCollection` | In-memory index of all hashes; source of English text and file locations | `src/Localization/Scanner/` |
| `StringHash` | Groups occurrences of one English string; provides `getText()` (→ `Text`) and `getStrings()` (→ `StringInfo[]`) | `src/Localization/Scanner/` |
| `Text` | Carrier of `getText()`, `getExplanation()`, `getHash()` | `src/Localization/Parser/Text.php` |
| `StringInfo` | One occurrence: `getSourceID()`, `getSourceFile()`, `getLanguageType()` | `src/Localization/Scanner/StringInfo.php` |
| `LocalizationTranslator` | Loads INI files; provides `getStrings(LocaleInterface): string[]` (hash → translation map) | `src/Localization/Translator/LocalizationTranslator.php` |
| `LocalizationWriter` | Writes hash→text pairs to an INI file | `src/Localization/Translator/LocalizationWriter.php` |
| `BaseLocalizationSource` | Owns the storage folder path (`getStorageFolder()`) and the source alias (`getAlias()`) | `src/Localization/Source/BaseLocalizationSource.php` |
| `ReleaseBuilder` | Pattern for self-contained Composer script entry points | `src/Tools/ReleaseBuilder.php` |
| `LocalizationException` | Base exception; error code ranges 39xxx used | `src/Localization/LocalizationException.php` |

### Existing INI file layout

```
{locale}-{source-alias}-server.ini   ← all strings (PHP + JS)
{locale}-{source-alias}-client.ini   ← JS-only strings (subset of server)
```
Files are located in `BaseLocalizationSource::getStorageFolder()`.

### Existing Composer script pattern

```json
"build": "\\AppLocalize\\Tools\\ReleaseBuilder::build"
```

Static `run()` / `build()` methods that `require_once` the autoloader, set up whatever they
need, and then proceed. No framework injection.

### Storage-folder constraint

The translator and writer both work relative to the source's
`getStorageFolder()`. Export files will live in the same folder so that no
extra path discovery is needed.

---

## Approach / Architecture

### New tool classes (in `src/Tools/`)

Two new static-entry-point classes following the `ReleaseBuilder` convention:

| Class | Namespace | Purpose |
|-------|-----------|---------|
| `TranslationExporter` | `AppLocalize\Tools` | Generates JSON export files from `storage.json` + existing INI translations |
| `TranslationImporter` | `AppLocalize\Tools` | Reads JSON export files and writes/updates INI translation files |

Both classes expose a `public static function run(): void` entry point for Composer and a
`public static function create(): self` factory for programmatic/test use.

### Configuration bootstrap

Both tools need a configured `Localization` environment (locales, sources, storage file).
The tools resolve configuration in this order:

1. Constant `LOCALIZATION_TOOLS_CONFIG` defined before the autoloader is required.
2. Environment variable `LOCALIZATION_TOOLS_CONFIG`.
3. Default fallback: `localization-tools-config.php` in the Composer project root
   (`__DIR__ . '/../../localization-tools-config.php'` from `src/Tools/`).

A reference config file for this package's own translations will be shipped as
`localization-tools-config.php` at the repository root.

### Export JSON format

File naming convention: `{locale}-{source-alias}-translations.json`  
Location: same directory as the INI files (`BaseLocalizationSource::getStorageFolder()`).

```json
{
  "format_version": 1,
  "locale": "de_DE",
  "locale_label": "German (Germany)",
  "source_alias": "application-localization",
  "source_label": "Application Localization",
  "exported_at": "2026-03-02T14:30:00+00:00",
  "strings": [
    {
      "hash": "d8b00929dec65d422303256336ada04f",
      "source_text": "Germany",
      "context": "",
      "files": [
        "src/Localization/Country/CountryDE.php:46",
        "src/Localization/Locale/de_DE.php:32"
      ],
      "translation": "Deutschland"
    },
    {
      "hash": "af4fb3fa4c5a75e5e5dcc8695e4c7f4b",
      "source_text": "Not yet translated string",
      "context": "Short description for translators",
      "files": [
        "src/SomeFile.php:12"
      ],
      "translation": ""
    }
  ]
}
```

**Key choices:**
- `"translation": ""` (empty string) signals "not yet translated". Importers skip empty entries.
- `"context"` maps directly to `Text::getExplanation()`.
- `"files"` is a `string[]` of `relativePath:lineNumber` pairs collected from all `StringInfo` occurrences for that hash.
- `format_version` enables future schema evolution without breaking imports.
- The native locale (`en_GB`) is always excluded from export (no need to translate English to English).

### Export logic (`TranslationExporter`)

```
TranslationExporter::run()
  └── loadConfig() → Localization::configure(...)
  └── for each registered app locale (excluding en_GB):
        └── for each registered source:
              └── scanner.load() → StringCollection
              └── translator.getStrings(locale) → hash→translated map
              └── getHashesBySourceID(source.getID()) → StringHash[]
              └── Build entries[]:
                    ├── hash
                    ├── source_text  (from StringHash::getText()->getText())
                    ├── context      (from StringHash::getText()->getExplanation())
                    ├── files        (from StringInfo::getSourceFile() + getLine())
                    └── translation  (from hash→translated map, "" if absent)
              └── json_encode(payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
              └── Write to {storageFolder}/{locale}-{sourceAlias}-translations.json
```

### Import logic (`TranslationImporter`)

```
TranslationImporter::run()
  └── loadConfig() → Localization::configure(...)
  └── for each registered app locale (excluding en_GB):
        └── for each registered source:
              └── Look for {storageFolder}/{locale}-{sourceAlias}-translations.json
              └── If not found: skip with warning
              └── Validate format_version
              └── scanner.load() → StringCollection (for existence check)
              └── Build hash→translation map (only entries with non-empty translation)
              └── Validate: warn on hashes not present in StringCollection (stale)
              └── Call LocalizationTranslator::save(source, collection) equivalent:
                    └── LocalizationWriter (server) ← all strings
                    └── LocalizationWriter (client) ← JS-only strings (filtered by StringHash::hasLanguageType('JavaScript'))
```

> **Note:** The importer does **not** round-trip through the HTTP editor workflow.
> It uses `LocalizationWriter` directly, the same underlying mechanism the editor uses.
> This keeps it independent of request context.

### Composer script additions

```json
"export-translations": "\\AppLocalize\\Tools\\TranslationExporter::run",
"import-translations": "\\AppLocalize\\Tools\\TranslationImporter::run"
```

---

## Rationale

- **JSON format** matches the codebase's own `storage.json` convention. No new parsing
  dependencies are required (`json_encode`/`json_decode` are built-in). LLMs and scripts
  consume JSON natively; human translators can edit it in any text editor or IDE.
- **Per-locale-per-source scope** mirrors the INI layout exactly, making diffs trivial and
  allowing partial re-exports (one locale, one source) without disturbing others.
- **Config file bootstrap** follows the same self-contained pattern as `ReleaseBuilder`
  while remaining usable by consumer applications through a config file convention.
- **Storage folder co-location** avoids introducing a new path concept and keeps export
  files next to the INI files they represent.
- **Empty string signals untranslated** (rather than `null`) because JSON tools and text
  editors handle empty strings more naturally for translation workflows.
- **No new runtime dependencies** — the export/import is a dev-time tool, all necessary
  classes (`LocalizationScanner`, `LocalizationTranslator`, `LocalizationWriter`) already
  exist.

---

## Detailed Steps

1. **Create `localization-tools-config.php`** at the project root. This file sets up
   `Localization` for the package's own strings: registers locales `de_DE` and `fr_FR`,
   the source folder `src/`, storage folder `localization/`, and calls `configure()`.

2. **Create `src/Tools/TranslationExporter.php`**
   - `public static function run(): void` — bootstrap entry point for Composer
   - `private static function loadConfig(): void` — resolves and `require`s the config file
   - `private function exportAll(): void` — iterates locales × sources
   - `private function exportLocaleSource(LocaleInterface $locale, BaseLocalizationSource $source): void` — builds and writes one JSON file
   - `private function buildEntry(StringHash $hash, string $translation): array` — assembles one string entry
   - `private function getFilePaths(StringHash $hash): string[]` — collects `relativePath:line` strings from all `StringInfo` for the hash
   - Error codes in the `394xx` range for new exceptions (if needed).

3. **Create `src/Tools/TranslationImporter.php`**
   - `public static function run(): void` — bootstrap entry point for Composer
   - `private static function loadConfig(): void` — same config resolution as exporter
   - `private function importAll(): void` — iterates locales × sources
   - `private function importLocaleSource(LocaleInterface $locale, BaseLocalizationSource $source): void` — reads JSON, validates, writes INI files
   - `private function writeIniFiles(LocaleInterface $locale, BaseLocalizationSource $source, StringCollection $collection, array $translations): void` — invokes `LocalizationWriter` for both server and client variants, filtering client strings by `StringHash::hasLanguageType('JavaScript')`
   - Error codes in the `395xx` range.

4. **Add Composer scripts** to `composer.json`:
   ```json
   "export-translations": "\\AppLocalize\\Tools\\TranslationExporter::run",
   "import-translations": "\\AppLocalize\\Tools\\TranslationImporter::run"
   ```

5. **Add test suite** `tests/testsuites/Tools/` with:
   - `TranslationExporterTest.php` — verifies JSON structure, field presence, locale/source filtering, skipping native locale
   - `TranslationImporterTest.php` — verifies INI file updates, stale-hash warnings, empty-translation skipping, client vs server file distinction
   - Add `<testsuite name="Tools">` to `phpunit.xml`

6. **Update project manifest** (see Required Components below).

---

## Dependencies

- `LocalizationScanner::load()` — must be called to populate `StringCollection` before export
- `LocalizationTranslator::getStrings(LocaleInterface)` — provides existing translations per locale
- `LocalizationWriter` — writes the INI output during import
- `StringCollection::getHashesBySourceID()` — isolates hashes belonging to one source
- `StringHash::getText()`, `StringHash::getStrings()`, `StringHash::hasLanguageType()` — string data access
- `BaseLocalizationSource::getStorageFolder()`, `::getAlias()`, `::getLabel()` — path and naming
- `Localization::getAppLocales()`, `Localization::getSources()` — iteration
- `Localization::BUILTIN_LOCALE_NAME` — to exclude `en_GB` from export

---

## Required Components

### New files
| File | Description |
|------|-------------|
| `src/Tools/TranslationExporter.php` | Export command class (new) |
| `src/Tools/TranslationImporter.php` | Import command class (new) |
| `localization-tools-config.php` | Default Localization setup for the package's own translations (new) |
| `tests/testsuites/Tools/TranslationExporterTest.php` | Unit tests for exporter (new) |
| `tests/testsuites/Tools/TranslationImporterTest.php` | Unit tests for importer (new) |

### Modified files
| File | Change |
|------|--------|
| `composer.json` | Add `export-translations` and `import-translations` scripts |
| `phpunit.xml` | Add `Tools` test suite entry |
| `docs/agents/project-manifest/api-translator-editor.md` | Document `TranslationExporter` and `TranslationImporter` |
| `docs/agents/project-manifest/file-tree.md` | Add new files |
| `docs/agents/project-manifest/tech-stack.md` | Add new Composer script entries |
| `docs/agents/project-manifest/api-events-exceptions.md` | Reserve `394xx` and `395xx` error code ranges |
| `AGENTS.md` | Add `export-translations` and `import-translations` to the Composer Scripts table |

---

## Assumptions

- The `storage.json` file has already been populated by a prior scan (`composer test` exercises this via the Scanner test suite). Export will log a warning and produce an empty strings array if `storage.json` is absent.
- The native locale `en_GB` is never exported (it is the source language itself).
- All locales currently registered via `Localization::getAppLocales()` at config-load time are exported/imported. The config file controls which locales are in scope.
- File paths stored in `StringInfo::getSourceFile()` are absolute; the exporter will make them relative by stripping the Composer project root prefix before writing to JSON.
- Client INI files are a strict subset of server INI files. The importer identifies client strings by checking `StringHash::hasLanguageType('JavaScript')`.

---

## Constraints

- **PHPStan level 8** must remain clean after the change. All new classes must be fully type-annotated.
- **`declare(strict_types=1)`** in every new PHP file.
- **No new runtime dependencies** — this is a dev/release tooling concern only.
- **Error codes** must be in the reserved ranges: `394xx` (exporter), `395xx` (importer).
- **`LocalizationWriter` must not be bypassed** — the importer must go through the same write path as the editor to ensure INI format consistency (hash files, escaping, header comments).
- **Canned classes must not be touched** — `CannedCountries`, `CannedCurrencies`, `CannedLocales` are out of scope.
- **The config file is opt-in** — if it is absent, the tools exit cleanly with an explanatory message rather than throwing an unhandled exception.

---

## Out of Scope

- GUI integration into the browser editor UI.
- Automatic machine translation (LLM invocation) — the export file is the handoff point.
- Differential / incremental export (only untranslated strings). All strings are always included so translators can review and correct existing translations.
- Support for multiple storage files or multi-source configurations beyond what `Localization::getSources()` provides.
- XLIFF or CSV output formats.
- Merging strategy when the same hash exists in multiple sources (by design, the same hash in two sources has the same English text and therefore the same translation; the importer writes the same translation to each source's INI file).

---

## Acceptance Criteria

- `composer export-translations` produces one JSON file per locale per source in the source's storage folder, named `{locale}-{source-alias}-translations.json`.
- Each JSON file contains `format_version`, `locale`, `locale_label`, `source_alias`, `source_label`, `exported_at`, and `strings` array.
- Each string entry contains `hash`, `source_text`, `context`, `files`, and `translation`.
- `"translation"` is `""` for strings not yet present in the INI file.
- `"translation"` is the current translated value for strings already in the INI file.
- `composer import-translations` reads those same JSON files and writes correct `server` and `client` INI files.
- Import skips entries with `"translation": ""`.
- Import logs a human-readable warning for hashes present in the JSON but absent from the current `StringCollection` (stale strings).
- The native locale `en_GB` is not exported.
- Both commands work with the included `localization-tools-config.php` on a clean checkout.
- PHPStan level 8 passes with no new errors.
- All new tests pass.
- Manifest documents are updated.

---

## Testing Strategy

### Unit tests
- **Exporter**: Instantiate with a mock `StringCollection` and `LocalizationTranslator`; verify the JSON payload structure, field values, and file-path formatting.
- **Importer**: Given a pre-written JSON fixture, verify that `LocalizationWriter` is called with the correct hash→text map; verify that client-only strings go to the client INI file.
- **Round-trip**: Export the package's own `localization/storage.json` to temp files; import them; assert the resulting INI content matches the originals.

### Manual verification
Run `composer export-translations` against this repository and inspect the generated JSON files in `localization/`. Run `composer import-translations` and confirm the INI files are unchanged (since the translations were already complete).

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`storage.json` not present** (no prior scan) | Exporter checks for file existence before `scanner.load()`; logs a clear error and exits gracefully instead of throwing. |
| **Stale hashes in import file** (string deleted from source since last export) | Importer validates each hash against the current `StringCollection`; logs a warning per stale hash but continues importing valid entries. |
| **Encoding issues in translations** | `LocalizationWriter::escapeIniValue()` already handles backslash, quote, `\n`, `\r`. JSON export uses `JSON_UNESCAPED_UNICODE` so UTF-8 strings round-trip cleanly. |
| **Config file not found** | Both tools detect the missing config file early, print an actionable error message, and exit with a non-zero code. |
| **Absolute paths in `StringInfo::getSourceFile()`** | The exporter normalises paths to be relative to the project root before writing. The importer never reads these paths back (they are informational only), so no reverse mapping is needed. |
| **Client vs server INI mismatch** | Import derives the client subset by testing `StringHash::hasLanguageType('JavaScript')` — the same criterion used by `ClientFilesGenerator`. |
