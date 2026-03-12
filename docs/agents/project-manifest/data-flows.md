# Key Data Flows

## 1. Application Bootstrap & Configuration

```
Application entry point (e.g., index.php)
    │
    ├── require vendor/autoload.php
    │     └── Localization::init() called automatically (end of Localization.php)
    │           ├── Localization::reset()
    │           │     └── Adds en_GB as default app + content locale
    │           └── Registers package's own source folder (localization/, src/)
    │
    ├── Localization::addAppLocale('de_DE')
    │     └── LocalesCollection::filterName() → createLocale() → stores in $locales['__application']
    │
    ├── Localization::addSourceFolder(alias, label, group, storageFolder, sourcesFolder)
    │     └── Creates FolderLocalizationSource, adds to $sources[], sorts by label
    │
    └── Localization::configure(storageFile, clientLibrariesFolder)
          ├── Sets $configured = true
          └── If clientLibrariesFolder is set:
                └── writeClientFiles() → ClientFilesGenerator::writeFiles()
```

## 2. Source Code Scanning (Finding Translatable Strings)

```
Localization::createScanner()
    └── new LocalizationScanner(storageFile)

scanner.scan()
    ├── For each registered source in Localization::getSources():
    │     └── source.scan(scanner)
    │           └── SourceScanner walks the source folder recursively
    │                 ├── Skips excluded folders/files
    │                 └── For each .php / .js file:
    │                       └── LocalizationParser::parseFile(path)
    │                             ├── Detects language by extension
    │                             ├── PHPLanguage: tokenizes with token_get_all()
    │                             └── JavaScriptLanguage: parses AST with Peast
    │                                   └── Extracts t(), tex(), pt(), etc. calls
    │                                         └── Creates Text objects (text, line, explanation)
    │
    ├── Results collected into StringCollection
    │     └── Grouped by StringHash (MD5 of English text)
    │           └── Each hash contains StringInfo entries (file, line, language, source)
    │
    └── Scanner saves collection to storage.json (serialized)
```

## 3. Runtime Translation (Server-Side)

```
Application code calls t('Some text', $arg1)
    │
    └── AppLocalize\t() [functions.php]
          └── Localization::getTranslator()->translate('Some text', [$arg1])
                │
                ├── First call: loads INI files for the active locale
                │     └── Reads {locale}-{source}-server.ini for each source
                │           └── Builds hash → translated_text map
                │
                ├── Computes MD5 hash of 'Some text'
                ├── Looks up hash in translations map
                │     ├── Found: uses translated text
                │     └── Not found: falls back to original English text
                │
                └── Applies sprintf() with $arg1 → returns final string
```

## 4. Runtime Translation (Client-Side)

```
Browser loads JavaScript includes:
    ├── md5.min.js          — MD5 hashing
    ├── translator.js       — AppLocalize_Translator + t() function
    └── locale-de.js        — Generated file registering translations:
          └── AppLocalize_Translator.a(hash1, text1).a(hash2, text2)...

Application JS calls t('Some text', arg1)
    └── AppLocalize_Translator.Translate('Some text', arg1)
          ├── Computes hex_md5('Some text')
          ├── Looks up hash in .strings registry
          │     ├── Found: uses translated text
          │     └── Not found: uses original text
          └── Applies sprintf() with arg1 → returns string
```

## 5. Client Library Generation

```
Localization::configure(storageFile, clientFolder)
    └── Localization::writeClientFiles()
          └── ClientFilesGenerator::writeFiles()
                │
                ├── Computes system key at call time:
                │     "Lib:{cacheKey}|System:{version}|Locales:{locale1,locale2,...}"
                │     (only non-native app locales appear in Locales: segment)
                │
                ├── Reads stored key from cachekey.txt (if present)
                │
                ├── Compares stored key === system key
                │     └── Equal + no force → skip (no-op); areFilesWritten() = true
                │
                ├── Copies static files to client folder:
                │     ├── translator.js
                │     └── md5.min.js
                │
                ├── For each registered app locale (non-native):
                │     └── Generates locale-{lang}.js
                │           ├── Loads server & client INI files
                │           ├── Filters to JavaScript-only strings
                │           └── Writes chained .a(hash, text) calls
                │
                └── Writes new system key to cachekey.txt
```

> **Call-ordering constraint:** `setClientLibrariesCacheKey()` and all `addAppLocale()` calls
> must be made **before** `configure()`. The system key is computed from the cache key and
> registered locale IDs at write time. See `constraints.md` → Client-Side Libraries for details.

> **Diagnostics:** Use `Localization::getClientFilesDiagnostics()` to inspect the stored key,
> computed system key, match status, client folder path, and cachekey.txt path without enabling
> echo-based logging. Use `ClientFilesGenerator::getWriteSkipReason()` to get a human-readable
> explanation of whether the next `writeFiles()` call would skip or write.

## 6. Translation Editing (Editor UI)

```
Browser hits editor endpoint (e.g., example/index.php)
    │
    ├── Localization configured (locales, sources, storage)
    ├── editor = Localization::createEditor()
    ├── editor.display()
    │     └── Renders full HTML page via PageScaffold.php
    │           ├── Source selector (tabs by group)
    │           ├── Locale tabs
    │           ├── Filter form (search, status)
    │           ├── Paginated string list
    │           └── Inline edit forms per string
    │
    ├── User triggers scan (GET ?scan=yes):
    │     └── scanner.scan() → re-analyzes all source files
    │                         → saves updated storage.json
    │
    └── User saves translations (POST):
          └── For each submitted hash → text pair:
                ├── translator.setTranslation(hash, text)
                └── translator.save(source, collection)
                      └── LocalizationWriter writes updated INI files
                            ├── {locale}-{source}-server.ini
                            └── {locale}-{source}-client.ini
                      └── ClientFilesGenerator refreshes locale-xx.js
```

## 7. Country / Currency / Locale / TimeZone Lookup

```
Localization::createCountries()
    └── CountryCollection::getInstance()
          └── BaseClassLoaderCollection loads Country/*.php via ClassRepositoryManager
                └── Cached in cache/class-repository-v1.php

countries->choose()->de()
    └── Returns CountryDE instance

country->getCurrency()
    └── new CountryCurrency(CurrencyCollection->getByISO(country->getCurrencyISO()), country)
          └── CountryCurrency provides localized formatting:
                makeReadable(1445.42) → "1.445,42 €" (DE) or "$1,445.42" (US)

country->getTimeZone()
    └── TimeZoneCollection->findByCountry(country)
          └── Returns matching CountryTimeZoneInterface
```

## 8. Event Flow

```
Localization::selectAppLocale('de_DE')
    └── selectLocaleByNS('de_DE', '__application')
          ├── Stores new locale in $selected['__application']
          ├── Resets $translator (forces reload on next translate())
          └── triggerEvent(EVENT_LOCALE_CHANGED, [...], LocaleChanged::class)
                └── For each registered listener:
                      └── callback(LocaleChanged $event, ...extraArgs)
                            ├── $event->getPrevious()   → old locale or null
                            ├── $event->getCurrent()    → new locale
                            ├── $event->getNamespace()  → '__application'
                            └── $event->isAppLocale()   → true
```
