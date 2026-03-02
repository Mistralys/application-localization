# Synthesis — Translation Export / Import Feature

**Plan:** `2026-03-02-translation-export-import`  
**Completed:** 2026-03-02  
**Status:** All 6 work packages COMPLETE — 0 regressions, PHPStan level 8 clean.

---

## Executive Summary

This plan delivered `composer export-translations` and `composer import-translations` as
a human- and machine-readable round-trip bridge between the scanner's `storage.json` and
the authoritative INI translation files. The feature was implemented, tested, and fully
documented across 6 work packages.

---

## Work Packages

| WP | Title | Status |
|---|---|---|
| WP-001 | Bootstrap config file (`localization-tools-config.php`) | COMPLETE |
| WP-002 | `TranslationExporter` implementation | COMPLETE |
| WP-003 | `TranslationImporter` implementation | COMPLETE |
| WP-004 | PHPUnit `Tools` suite wiring (`phpunit.xml`) | COMPLETE |
| WP-005 | Test suite (`TranslationExporterTest`, `TranslationImporterTest`, `RoundTripTest`) | COMPLETE |
| WP-006 | Manifest & documentation updates | COMPLETE |

---

## Deliverables

### New Source Files

| File | Purpose |
|---|---|
| `localization-tools-config.php` | Default Localization bootstrap for the Composer tools |
| `src/Tools/TranslationExporter.php` | `composer export-translations` entry point |
| `src/Tools/TranslationImporter.php` | `composer import-translations` entry point |

### New Test Files

| File | Tests |
|---|---|
| `tests/testsuites/Tools/TranslationExporterTest.php` | 6 tests covering JSON structure, locale exclusion, empty/translated values, relative paths |
| `tests/testsuites/Tools/TranslationImporterTest.php` | 5 tests covering INI writes, stale hash warnings, client/server split, missing file |
| `tests/testsuites/Tools/RoundTripTest.php` | 2 integration tests: full export→import cycle preserves all translations |

### Updated Manifest Files

| File | Changes |
|---|---|
| `docs/agents/project-manifest/api-translator-editor.md` | New `## Translation Tools` section: config resolution, JSON format, public APIs, key behaviours |
| `docs/agents/project-manifest/file-tree.md` | Added test files under `testsuites/Tools/`; root files (`localization-tools-config.php`) and `src/Tools/` entries already present |
| `docs/agents/project-manifest/tech-stack.md` | Added `export-translations` and `import-translations` rows to Build & Tooling table |
| `docs/agents/project-manifest/api-events-exceptions.md` | Reserved error-code ranges `394xx` (TranslationExporter) and `395xx` (TranslationImporter) |
| `AGENTS.md` | Added `#### Translation` subsection with both Composer commands |

---

## Test Metrics (Final)

| Suite | Tests | Assertions | Result |
|---|---|---|---|
| Tools | 13 | 599 | PASS |
| Full suite | 108 | 917 | PASS |
| PHPStan level 8 | 168 files | — | 0 errors |

---

## Key Design Decisions

1. **Config resolution order** — constant → env var → default fallback. Allows both CLI
   and programmatic use without coupling the tools to a specific project layout.

2. **`create()` + `export()` / `import()` factory methods** — added to both classes to
   enable programmatic test usage without side-effecting config loading. Follows the
   `ReleaseBuilder` pattern already in the codebase.

3. **Source deduplication** — both classes skip duplicate source registrations (same alias
   + storage folder) to prevent writing the same file twice.

4. **Native locale excluded** — `en_GB` (the builtin locale) is skipped in both export and
   import loops. There is nothing to translate for the source language.

5. **Client vs server INI split** — the importer writes all translated strings to the
   server INI and only JavaScript-tagged strings to the client INI, matching the pre-existing
   INI file layout.

---

## No Regressions

All 95 pre-existing tests continued to pass (108 total = 95 baseline + 13 new).
PHPStan level 8 clean across all 168 files.
