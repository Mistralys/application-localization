# Synthesis — Editor Placeholder Validation

**Project:** 2026-03-01-editor-placeholder-validation  
**Completed:** 2026-03-01  
**Status:** All 6 work packages COMPLETE

---

## What Was Delivered

### Tier 1 — Client-side validation (`src/js/editor.js`)

- Added `PlaceholderValidator` sub-object to `Editor` with:
  - `getNumbers(text)` — extracts sorted unique numbered argument indices from a sprintf string using `/%([0-9]+)\$/g`.
  - `equal(a, b)` — deep integer-array comparison.
- Updated `Editor.Confirm(hash)` to read `data-native-text` from the row element and block confirmation with an `alert-danger .placeholder-warning` div if the translation's numbered argument set differs from the source. The warning is cleared automatically when sets match on a subsequent OK click.

### Tier 2 — Server-side save guard (`src/Localization/Editor/LocalizationEditor.php`)

- Added server-side placeholder mismatch guard in `executeSave()`: looks up source text via `$collection->hashExists()` / `getHash()`, compares sorted `getPlaceholderNumbers()` arrays, skips `setTranslation()` and adds a `MESSAGE_WARNING` session message if sets differ.
- Unknown hashes pass through without blocking (null guard).

### Tier 3 — Source quality badge (`src/Localization/Editor/Template/PageScaffold.php`)

- Added `data-native-text` attribute to the `<tr class="string-entry">` row element (HTML-attribute-escaped), enabling the JS validator to read native text without a server round-trip.
- Added conditional `alert-warning` block in `renderTextEditorEntry()` after the explanation block for source strings that contain unnumbered placeholders (e.g. `%s`). Block is inside `.string-form` (only shown when row is expanded).

### PHP Helper Methods (`LocalizationEditor`)

- **`detectVariables(string $string): string[]`** — refactored from hand-rolled regex to `FormatParser`-based implementation returning `getFormatString()` tokens.
- **`getPlaceholderNumbers(string $text): int[]`** — returns sorted unique numbered argument indices; `[]` on empty or exception.
- **`hasUnnumberedPlaceholders(string $text): bool`** — returns `true` if any placeholder lacks a positional argument number; `false` on exception.

### Documentation (`docs/agents/project-manifest/api-translator-editor.md`)

- Updated `detectVariables()` entry to reference FormatParser.
- Added entries for `getPlaceholderNumbers()` and `hasUnnumberedPlaceholders()` with signatures and descriptions.

---

## Files Modified

| File | Change |
|---|---|
| `src/Localization/Editor/LocalizationEditor.php` | New helper methods, refactored `detectVariables()`, server-side save guard |
| `src/Localization/Editor/Template/PageScaffold.php` | `data-native-text` attribute, unnumbered-placeholder warning badge |
| `src/js/editor.js` | `PlaceholderValidator` sub-object, `Confirm()` client-side validation |
| `docs/agents/project-manifest/api-translator-editor.md` | New method entries |

---

## Verification

- `composer analyze` (PHPStan level 8): **no errors**
- `composer test` (PHPUnit): **95/95 tests pass**
