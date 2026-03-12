# Plan

## Summary

Add placeholder validation to the browser-based translation editor UI so that:

1. **Translation validation:** When a translator submits a translated string, the system checks that the translated text uses the same set of numbered sprintf argument indices as the source (native) string. Mismatches are surfaced as both an immediate client-side warning (before confirmation) and a server-side guard that skips saving invalid translations.
2. **Source quality warnings:** Source strings that contain unnumbered sprintf placeholders (e.g., `%s`, `%d` without a positional argument like `%1$s`) render an inline notice within the editor entry, reminding maintainers to fix those strings to comply with the project's numbering convention.

Both checks use the already-installed `mistralys/php-sprintf-parser` library on the PHP side and a lightweight inline regex on the JS side.

---

## Architectural Context

| Component | File | Role |
|---|---|---|
| Editor controller | `src/Localization/Editor/LocalizationEditor.php` | Handles request dispatch (`executeSave()`, `detectVariables()`), exposes helper methods to templates |
| Editor template | `src/Localization/Editor/Template/PageScaffold.php` | Renders all HTML; `renderTextEditorEntry()` outputs each source string plus its translation textarea |
| Editor JS | `src/js/editor.js` | jQuery-based; `Editor.Confirm(hash)` runs when the user clicks OK on a translation row |
| Sprintf parser library | `vendor/mistralys/php-sprintf-parser/src/FormatParser.php` | Already installed; provides `getPlaceholders()` returning `Placeholder[]`, each with `isNumbered()` / `getNumber()` |
| String collection | `src/Localization/Scanner/StringCollection.php` | `getHash(string $hash) : StringHash` — looks up a source string by MD5 hash |
| String hash/info | `src/Localization/Scanner/StringHash.php`, `StringInfo.php` | `StringHash::getText() : StringInfo`, `StringInfo::getText() : string` — retrieves native text |

The current `detectVariables()` on `LocalizationEditor` uses a hand-rolled limited regex (`/%[0-9]+d|%s|%[0-9]+\$s/i`) that is neither exhaustive nor consistent with the parser library. This plan replaces that logic with `FormatParser` to establish a single source of truth.

The editor UI currently renders each string's native text in a `<p class="native-text">` inside the hidden form row, but does not expose it as a machine-readable data attribute for JS consumption.

---

## Approach / Architecture

### Tier 1 — Client-side validation (editor.js)

Extend `Editor.Confirm(hash)` to, before finalising, extract the set of numbered argument indices from both:
- The native (source) text, read from a new `data-native-text` attribute added to the `<tr class="string-entry">` element.
- The textarea value.

If the sets are different (by size or by membership), inject an inline `<div class="alert alert-danger">` warning below the textarea identifying the mismatch and **do not confirm**. Provide a **"Save anyway"** button data-attribute flag so a translator who is intentionally omitting a placeholder (e.g., the language makes it implicit) can force the confirmation on the second click.

This gives instant, zero-roundtrip feedback.

### Tier 2 — Server-side save guard (LocalizationEditor.php)

In `executeSave()`, before calling `$translator->setTranslation()` for each hash/text pair, look up the source string via `$this->scanner->getCollection()->getHash($hash)`, retrieve its native text, and compare placeholder number sets using the new PHP helper methods. Translations that fail the check are **skipped** (not persisted) and a per-string warning message is appended to the session. After the redirect, these warnings are displayed via the existing `renderUIMessages()` mechanism.

This provides a safe server-side backstop even if JS is disabled or bypassed.

### Tier 3 — Source quality badge (PageScaffold.php)

Inside `renderTextEditorEntry()`, after the context/explanation block, conditionally render a `<div class="alert alert-warning">` badge when the source string's native text contains any **unnumbered** placeholders. This badge:
- Is shown only within the open editing form (inside `.string-form td`), not in the collapsed row.
- Describes which unnumbered placeholders were detected.
- Is informational — it does not block saving.

---

## Rationale

- **Using `FormatParser` on the PHP side** is the natural choice: it is already a declared runtime dependency (`tech-stack.md`) and provides typed `Placeholder` objects with `isNumbered()` / `getNumber()`.
- **Inline JS regex instead of importing a library** keeps `editor.js` self-contained (it is inlined into the page scaffold via `getJavascript()` and must have no external dependencies beyond jQuery).
- **Comparing argument number sets, not format strings** correctly allows `%1$s` in the source to match `%1$02d` in the translation — i.e., only the position number must be preserved, the specifier and flags may vary freely.
- **Skipping (not rejecting) on server save** mirrors the existing save UX: the page redirects after save and shows session messages, so individual per-string failures are reported without blocking the entire batch.
- **Two-tier approach** prevents data loss from JS bugs while still giving immediate UX feedback.

---

## Detailed Steps

### Step 1 — Add helper methods to `LocalizationEditor`

In `src/Localization/Editor/LocalizationEditor.php`:

1.1. **Refactor `detectVariables(string $string): string[]`** — Replace its hand-rolled regex body with a `FormatParser`-based implementation that collects `$placeholder->getFormatString()` for each detected placeholder. This is a drop-in replacement because `renderText()` in `PageScaffold` only needs the matched strings.

1.2. **Add `getPlaceholderNumbers(string $text): int[]`** — Uses `FormatParser` to extract only numbered placeholders and returns an array of their argument numbers (e.g., `[1, 2]` for `"Hello %1$s, you have %2$d messages"`). Returns an empty array if no numbered placeholders are found.

1.3. **Add `hasUnnumberedPlaceholders(string $text): bool`** — Returns `true` if `FormatParser` detects any placeholder where `isNumbered()` returns `false`.

Add `use Mistralys\SprintfParser\FormatParser;` to the imports of `LocalizationEditor`.

---

### Step 2 — Update `executeSave()` in `LocalizationEditor`

In the `foreach($strings as $hash => $text)` loop within `executeSave()`:

2.1. After trimming `$text`, before calling `$translator->setTranslation()`:

```php
// Guard: look up source text and compare placeholder number sets
$collection = $this->scanner->getCollection();
if($collection->hashExists($hash)) {
    $sourceText = $collection->getHash($hash)->getText();
    if($sourceText !== null) {
        $sourceNumbers = $this->getPlaceholderNumbers($sourceText->getText());
        $translationNumbers = $this->getPlaceholderNumbers($text);
        sort($sourceNumbers);
        sort($translationNumbers);
        if($sourceNumbers !== $translationNumbers) {
            $this->addMessage(
                t(
                    'The translation for "%1$s" was not saved: placeholder mismatch (source uses %2$s, translation uses %3$s).',
                    mb_substr($sourceText->getText(), 0, 60),
                    implode(', ', array_map(static fn(int $n) => '%'.$n.'$…', $sourceNumbers)),
                    implode(', ', array_map(static fn(int $n) => '%'.$n.'$…', $translationNumbers))
                ),
                self::MESSAGE_WARNING
            );
            continue;
        }
    }
}
```

2.2. Add the necessary `use` import for `FormatParser` (already done in Step 1.1).

---

### Step 3 — Expose native text as a data attribute in `PageScaffold`

In `renderTextEditorEntry(StringHash $string)` in `src/Localization/Editor/Template/PageScaffold.php`:

3.1. On the `<tr class="string-entry inactive" ...>` element, add:

```php
data-native-text="<?php echo $this->esc($text->getText()) ?>"
```

This places the raw UTF-8 source text (HTML-attribute-escaped) directly on the row element so `editor.js` can read it without a server round-trip.

---

### Step 4 — Add unnumbered-placeholder source warning in `PageScaffold`

In `renderTextEditorEntry()`, after the existing context/explanation block (the `if(!empty($explanation))` block), add a new conditional block:

```php
<?php
if($this->editor->hasUnnumberedPlaceholders($text->getText()))
{
    ?>
    <div class="alert alert-warning py-1 mt-1 mb-1">
        <i class="fas fa-exclamation-triangle"></i>
        <?php pt('This source text contains unnumbered placeholders (e.g. %1$s instead of %2$s). Renumber them to ensure translations are language-independent.', '%s', '%1$s') ?>
    </div>
    <?php
}
?>
```

`$text` is the `StringInfo` object already retrieved earlier in `renderTextEditorEntry()`.

---

### Step 5 — Add client-side placeholder validation in `editor.js`

5.1. Add a `PlaceholderValidator` sub-object to the `Editor` object:

```javascript
PlaceholderValidator: {
    /**
     * Extracts numbered argument indices from a sprintf format string.
     * Matches patterns like %1$s, %2$02d, %3$-10.5f …
     * Returns a sorted array of unique integer argument numbers.
     * @param {string} text
     * @returns {number[]}
     */
    getNumbers: function(text) {
        var matches = text.match(/%(\d+)\$/g) || [];
        var nums = matches.map(function(m) {
            return parseInt(m.replace('%', '').replace('$', ''), 10);
        });
        nums = nums.filter(function(v, i, a) { return a.indexOf(v) === i; });
        nums.sort(function(a, b) { return a - b; });
        return nums;
    },

    /**
     * Returns true if the arrays contain the same integers in the same order.
     * @param {number[]} a
     * @param {number[]} b
     * @returns {boolean}
     */
    equal: function(a, b) {
        if(a.length !== b.length) { return false; }
        for(var i = 0; i < a.length; i++) {
            if(a[i] !== b[i]) { return false; }
        }
        return true;
    }
}
```

5.2. Update `Editor.Confirm(hash)`:

After retrieving `value` and before updating the UI, read the native text from the row element and validate. If the placeholder sets do not match, show an error and **block the save with no override**:

```javascript
Confirm: function(hash)
{
    this.DetectElement(hash);

    var value = this.textarea.val();
    value = value.trim();

    if(value == '') {
        this.textarea.focus();
        return;
    }

    // Placeholder validation
    var nativeText = this.el.data('native-text') || '';
    var sourceNums = this.PlaceholderValidator.getNumbers(nativeText);

    if(sourceNums.length > 0) {
        var translationNums = this.PlaceholderValidator.getNumbers(value);

        if(!this.PlaceholderValidator.equal(sourceNums, translationNums)) {
            var warningEl = this.form.find('.placeholder-warning');
            if(warningEl.length === 0) {
                warningEl = $('<div class="alert alert-danger placeholder-warning"></div>');
                this.textarea.after(warningEl);
            }
            warningEl.html(
                '<i class="fa fa-exclamation-triangle"></i> ' +
                '<strong>Placeholder mismatch:</strong> ' +
                'Source uses ' + (sourceNums.length > 0 ? sourceNums.map(function(n){ return '%'+n+'$\u2026'; }).join(', ') : 'none') +
                ', translation uses ' + (translationNums.length > 0 ? translationNums.map(function(n){ return '%'+n+'$\u2026'; }).join(', ') : 'none') +
                '. Please correct the translation to match the source placeholders.'
            );
            this.textarea.focus();
            return;
        } else {
            // Counts match — remove any stale warning
            this.form.find('.placeholder-warning').remove();
        }
    }

    // save the trimmed value
    this.textarea.val(value);
    // ... existing status icon / table row update code ...
}
```

There is no override mechanism: the translator must correct the translation before it can be confirmed.

---

## Dependencies

- `mistralys/php-sprintf-parser` ^1.0 — already declared in `composer.json`, already installed.
- jQuery — already loaded in the page scaffold.
- `StringCollection::hashExists(string): bool` — already exists (line 94 of `StringCollection.php`).
- `StringCollection::getHash(string): StringHash` — already exists (line 102 of `StringCollection.php`).
- `StringHash::getText(): ?StringInfo` — need to verify the exact return type; use `null` guard.

---

## Required Components

### Modified (existing)
- `src/Localization/Editor/LocalizationEditor.php` — add `getPlaceholderNumbers()`, `hasUnnumberedPlaceholders()`, refactor `detectVariables()`, update `executeSave()`.
- `src/Localization/Editor/Template/PageScaffold.php` — add `data-native-text` attribute, add unnumbered-placeholder warning block.
- `src/js/editor.js` — add `PlaceholderValidator` sub-object, update `Editor.Confirm()`.

### Documentation
- `docs/agents/project-manifest/api-translator-editor.md` — document the two new public methods on `LocalizationEditor`.

---

## Assumptions

- `StringHash::getText()` returns a `?StringInfo` (nullable). A `null` guard is used in `executeSave()` to silently skip the check for hashes without resolved text (conservative — does not block save for unknown hashes).
- The `editor.js` file is always served inline (via `getJavascript()` in `PageScaffold`), so there is no separate minification/build step needed.
- Placeholder count mismatches are a hard block: no override is provided in either the client-side or server-side validation path.
- The server-side warning messages use the existing `addMessage()` / `$_SESSION['localization_messages']` path, so no new infrastructure is needed for displaying them.

---

## Constraints

- Editor UI must stay within Bootstrap 4, Font Awesome, and jQuery — no new CDN dependencies.
- PHPStan level 8 must pass after changes; new methods must have complete type declarations and docblocks.
- `detectVariables()` must remain public with the same signature (`string $string): string[]`) as it is referenced in `PageScaffold::renderText()`.
- `CannedCountries`, `CannedCurrencies`, `CannedLocales` are not touched.

---

## Out of Scope

- Scanner-level (scan-time) lint warnings for unnumbered placeholders in source strings — this would be a separate feature touching `LocalizationScanner` and `CollectionWarning`.
- PHPUnit tests for the new PHP helper methods (recommended as a follow-up, not part of this plan).
- Changing the `translator.js` client library.
- Editing the `editor.css` stylesheet beyond what is strictly necessary for the new warning elements (Bootstrap's standard `alert` classes are used).
- Updating the `changelog.md` (handled separately by the release process).

---

## Acceptance Criteria

1. When a user enters a translation that uses a different set of numbered argument indices than the source string and clicks OK, an inline `alert-danger` block appears and the form row does **not** close. There is no way to bypass this check.
2. On a page-reload after save, if the server-side guard catches a mismatch (e.g., JS was disabled or bypassed), a `MESSAGE_WARNING` session message is displayed identifying which string was skipped and why.
4. Source strings containing unnumbered placeholders (`%s`, `%d`, etc.) show an `alert-warning` block inside their editor form row; strings without unnumbered placeholders do not show the block.
5. Source strings that use only numbered placeholders but no placeholders at all (count = 0) do not trigger the placeholder mismatch check in either JS or PHP.
6. `composer analyze` (PHPStan level 8) passes without new errors.
7. The `renderText()` helper continues to correctly highlight all placeholders (including more exotic forms like `%02d`) after `detectVariables()` is refactored to use `FormatParser`.

---

## Testing Strategy

- **Manual:** Load the editor with a source that has `%1$s %2$d`. Attempt translations with: (a) matching placeholders `%1$02d %2$s`, (b) missing one placeholder, (c) extra placeholder, (d) reordered but complete placeholders. Confirm client-side error blocks (b) and (c); no warning for (a) and (d) (complete set in any order is valid).
- **Manual:** Verify the server-side guard by disabling JS in the browser, submitting a mismatched translation, and confirming the session warning message is displayed.
- **Manual:** Add a source string with `%s` (unnumbered) and confirm the source quality badge appears in the editor.
- **PHPStan:** Run `composer analyze` and confirm zero new errors.
- **Regression:** Run `composer test` and confirm all suites pass.

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **`StringHash::getText()` returns `null`** for hashes that exist in the collection but have no resolved text | Use a `null !== $sourceText` guard in `executeSave()` to skip the check silently for those hashes. |
| **`FormatParser` throws `ParserException`** on malformed input | Wrap `FormatParser` calls in `try/catch` inside the new helper methods; return an empty array / `false` on exception so degraded behaviour is safe. |
| **Translator needs to intentionally omit a placeholder** | No override is provided by design. If a genuine use case emerges, a `data-force` attribute bypass can be added later as a deliberate feature request. |
| **`detectVariables()` regex replacement breaks placeholder highlighting** in `renderText()` | The refactored method must return `$placeholder->getFormatString()` strings; these are the full matched tokens (e.g., `%1$s`), so `str_replace` in `renderText()` continues to work without change. Covered by manual regression check. |
| **Performance** of calling `FormatParser` per-string in `renderTextEditorEntry()` | `FormatParser` is a pure in-memory regex operation; for the typical page size (dozens of strings), this is negligible. |
