# AGENTS.md — Application Localization

> **Rule:** This file is the operating manual for any AI agent entering this
> codebase. Read it first. Follow it exactly.

---

## 1. Project Manifest — Start Here!

The Project Manifest is the **canonical source of truth** for this project.
Read it before reading any source code.

**Location:** `docs/agents/project-manifest/`

| Document | Description |
|---|---|
| [README.md](docs/agents/project-manifest/README.md) | Manifest index and section map. |
| [tech-stack.md](docs/agents/project-manifest/tech-stack.md) | PHP 8.4+, Composer, dependencies, JS components, build tools. |
| [file-tree.md](docs/agents/project-manifest/file-tree.md) | Annotated directory structure. |
| [api-core.md](docs/agents/project-manifest/api-core.md) | `Localization` static facade and global translation functions (`t()`, `pt()`, etc.). |
| [api-countries-currencies.md](docs/agents/project-manifest/api-countries-currencies.md) | 19 countries, 10 currencies, country-currency formatting. |
| [api-locales-timezones.md](docs/agents/project-manifest/api-locales-timezones.md) | 18 locales, 21 timezones, baskets. |
| [api-parser-scanner.md](docs/agents/project-manifest/api-parser-scanner.md) | PHP/JS source parser, scanner, string collection, source subsystem. |
| [api-translator-editor.md](docs/agents/project-manifest/api-translator-editor.md) | Runtime translator, INI writer, client file generator, browser editor UI. |
| [api-events-exceptions.md](docs/agents/project-manifest/api-events-exceptions.md) | Event system and exception hierarchy with error code ranges. |
| [data-flows.md](docs/agents/project-manifest/data-flows.md) | 8 key flows: bootstrap, scanning, translation, editor, lookups, events. |
| [constraints.md](docs/agents/project-manifest/constraints.md) | Coding standards, alias handling, class generation, testing conventions. |

### Quick Start Workflow

1. **Read** `README.md` — understand the section map.
2. **Read** `tech-stack.md` — understand runtime, dependencies, and architecture patterns.
3. **Read** `constraints.md` — internalize coding rules and non-obvious gotchas.
4. **Read** `file-tree.md` — know where everything lives.
5. **Reference** the relevant `api-*.md` and `data-flows.md` as needed for the task at hand.

---

## 2. Manifest Maintenance Rules

When you make a code change, you **MUST** update the corresponding manifest
documents to prevent drift.

| Change Made | Documents to Update |
|---|---|
| New country class added (`Country/*.php`) | `api-countries-currencies.md`, `file-tree.md`, `constraints.md` (clear cache note) |
| New currency class added (`Currency/*.php`) | `api-countries-currencies.md`, `file-tree.md` |
| New locale class added (`Locale/*.php`) | `api-locales-timezones.md`, `file-tree.md` |
| New timezone class added (`TimeZone/**/*.php`) | `api-locales-timezones.md`, `file-tree.md` |
| Dependency added or removed | `tech-stack.md` |
| Directory restructured | `file-tree.md` |
| New public method on `Localization` facade | `api-core.md` |
| New public method on any other class | Relevant `api-*.md` |
| New event class added | `api-events-exceptions.md` |
| New exception or error code added | `api-events-exceptions.md` |
| New data flow or changed interaction path | `data-flows.md` |
| Coding convention or constraint changed | `constraints.md` |
| New source or build command | `tech-stack.md` |
| Translation function added or renamed | `api-core.md`, `constraints.md` |
| Canned class templates changed | `constraints.md` |
| Version bumped | `README.md` (manifest header) |

---

## 3. Efficiency Rules — Search Smart

Do not scan source files when the answer is already in the manifest.

- **Finding a file or class?** → Check `file-tree.md` FIRST.
- **Understanding a method signature?** → Check the relevant `api-*.md` FIRST.
- **Understanding how subsystems interact?** → Check `data-flows.md` FIRST.
- **Checking what framework or pattern to use?** → Check `tech-stack.md` FIRST.
- **Checking naming or coding rules?** → Check `constraints.md` FIRST.
- **Only then** read source files for implementation details the manifest does not cover.

---

## 4. Failure Protocol & Decision Matrix

| Scenario | Action | Priority |
|---|---|---|
| Ambiguous requirement | Use the most restrictive interpretation consistent with existing patterns. | MUST |
| Manifest contradicts source code | Trust the manifest. Flag the code as a potential bug. | MUST |
| Source code contradicts manifest | Verify via tests. If tests pass, flag the manifest for update. | MUST |
| Missing documentation for a class/method | Flag the gap explicitly. Do not invent documentation. | MUST |
| Adding a new country/locale/currency/timezone | Follow the existing class pattern exactly; clear class cache after. | MUST |
| Modifying a Canned class | Do NOT edit `CannedCountries`, `CannedCurrencies`, or `CannedLocales` directly — these are auto-generated. Modify the templates in `src/Tools/Templates/` instead. | MUST |
| Untested code path | Proceed with caution. Add a test recommendation in your output. | SHOULD |
| PHP version ambiguity | Target PHP 8.4 syntax. Modern PHP features (union types, enums, readonly, etc.) are allowed. | MUST |
| Placeholder formatting in translations | Always use numbered sprintf placeholders (`%1$s`, `%2$d`). Never use positional `%s`. | MUST |
| Editor UI changes | The editor uses Bootstrap, Font Awesome, jQuery, and HTML_QuickForm2. Stay within those. | SHOULD |
| Locale alias edge case (`uk`/`gb`, `en_UK`/`en_GB`) | The system normalizes these automatically. Do not add special handling. | SHOULD |
| Static analysis fails | PHPStan must pass at level 8. Fix before committing. Config: `phpstan.neon` (project root). | MUST |

---

## 5. Project Stats

| Item | Value |
|---|---|
| **Language / Runtime** | PHP 8.4+, JavaScript (vanilla) |
| **Architecture** | Static facade (`Localization`), dynamic class loading via `BaseClassLoaderCollection` |
| **Package Manager** | Composer |
| **Test Framework** | PHPUnit ≥ 13.0 |
| **Static Analysis** | PHPStan level 8 (`phpstan.neon` in project root) |
| **Build Tool** | `composer build` → `ReleaseBuilder::build()` (generates canned classes + docs) |
| **Key Namespace** | `AppLocalize\` → `src/` |
| **Translation Storage** | INI files per locale per source (server + client variants) |
| **Supported Entities** | 19 countries, 10 currencies, 18 locales, 21 timezones |

### Composer Scripts

#### Build & Cache

| Command | Description |
|---|---|
| `composer build` | Generate canned classes + docs |
| `composer clear-class-cache` | Clear the class loader cache |

#### PHPStan (Static Analysis)

| Command | Description |
|---|---|
| `composer analyze` | Run PHPStan analysis (level 8, `phpstan.neon`) |
| `composer analyze-save` | Run PHPStan analysis, save output to `phpstan-result.txt` |
| `composer analyze-clear` | Clear PHPStan result cache |

#### PHPUnit (Testing)

> **Agent note:** The `test-file`, `test-suite`, `test-filter` and `test-group`
> scripts use `--no-progress` to suppress progress bars, producing clean
> line-based output suitable for automated parsing.

| Command | Syntax | Description |
|---|---|---|
| `composer test` | `composer test` | Run the full test suite |
| `composer test-file` | `composer test-file -- path/to/TestFile.php` | Run all tests in a single file |
| `composer test-suite` | `composer test-suite -- SuiteName` | Run a named test suite (see `phpunit.xml` for suite names) |
| `composer test-filter` | `composer test-filter -- testMethodName` | Run tests matching a name/regex filter |
| `composer test-group` | `composer test-group -- groupName` | Run tests annotated with a `@group` tag |
