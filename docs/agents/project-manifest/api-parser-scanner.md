# Public API — Parser & Scanner

## Parser Subsystem

**Namespace:** `AppLocalize\Localization\Parser`

Responsible for analyzing PHP and JavaScript source files to extract translatable strings
(calls to `t()`, `tex()`, `pt()`, etc.).

### `LocalizationParser`

Orchestrates parsing of individual files or raw code strings.

```php
public const ERROR_INVALID_LANGUAGE_ID = 40601;
public const ERROR_UNSUPPORTED_FILE_EXTENSION = 40602;
public const ERROR_INVALID_LANGUAGE_CLASS = 40603;

public function __construct(LocalizationScanner $scanner);
public function parseFile(string $path) : BaseLanguage;
public function parseString(string $languageID, string $code) : BaseLanguage;
public function getLanguageIDs() : array;
public function createLanguage(string $languageID) : BaseLanguage;
public function isExtensionSupported(string $ext) : bool;
public function isFileSupported(string $file) : bool;
```

### `BaseLanguage` (abstract)

Base class for language-specific parsers. Two concrete implementations exist:
- `Language\PHPLanguage` — Parses PHP files using PHP's built-in tokenizer
- `Language\JavaScriptLanguage` — Parses JavaScript files using the Peast AST parser

```php
public const ERROR_SOURCE_FILE_NOT_FOUND = 40501;
public const ERROR_FAILED_READING_SOURCE_FILE = 40502;

public function __construct(LocalizationParser $parser);

abstract public function getID() : string;
abstract public function getTokenClass() : string;

public function hasSourceFile() : bool;
public function getSourceFile() : string;
public function parseFile(string $path) : void;
public function parseString(string $content) : void;
public function getTexts() : Text[];
public static function getAllowedContextTags() : string[];
public function getFunctionNames() : string[];
public function hasWarnings() : bool;
public function getWarnings() : ParserWarning[];
public function getWarningsAsString() : string;
public function trimText(string $text) : string;
```

### `BaseParsedToken` (abstract)

Wrapper around language-specific tokens. Two concrete implementations:
- `Token\PHPToken`
- `Token\JavaScriptToken`

```php
public function __construct($definition, ?BaseParsedToken $parentToken = null);
public function getValue() : ?string;
public function getToken() : string;
public function getLine() : int;
public function toArray() : array;

abstract public function isOpeningFuncParams() : bool;
abstract public function isClosingFuncParams() : bool;
abstract public function getFunctionNames() : string[];
abstract public function isEncapsedString() : bool;
abstract public function isTranslationFunction() : bool;
abstract public function isVariableOrFunction() : bool;
abstract public function isExplanationFunction() : bool;
abstract public function isArgumentSeparator() : bool;
```

### `Text`

Represents a single translatable text extracted from source code.

```php
public const SERIALIZED_TEXT = 'text';
public const SERIALIZED_LINE = 'line';
public const SERIALIZED_EXPLANATION = 'explanation';

public function __construct(string $text, int $line, string $explanation = '');
public function getExplanation() : string;
public function isEmpty() : bool;
public function getLine() : int;
public function getText() : string;
public function getHash() : string;
public function toArray() : array;
public static function fromArray(array $array) : Text;
```

### `ParserWarning`

Captures issues encountered during parsing (e.g., concatenated strings, variables in translation calls).

```php
public function __construct(BaseLanguage $language, BaseParsedToken $token, string $message);
public function getLanguage() : BaseLanguage;
public function getToken() : BaseParsedToken;
public function getFile() : string;
public function getLine() : int;
public function getMessage() : string;
public function toArray() : array;
public function toString() : string;
```

---

## Scanner Subsystem

**Namespace:** `AppLocalize\Localization\Scanner`

Coordinates scanning across all registered source folders and maintains the
collected string inventories.

### `LocalizationScanner`

Top-level scanner that iterates over all registered sources.

```php
public function __construct(string $storageFile);
public function isScanAvailable() : bool;
public function scan() : void;
public function load() : void;
public function getParser() : LocalizationParser;
public function getCollection() : StringCollection;
public function getExecutionTime() : float;
public function countHashes() : int;
public function countFiles() : int;
public function hasWarnings() : bool;
public function countWarnings() : int;
public function getWarnings() : CollectionWarning[];
```

### `StringCollection`

In-memory storage of all discovered translatable strings, indexed by hash.

```php
public const ERROR_UNKNOWN_STRING_HASH = 39201;
public const SOURCE_FILE = 'file';
public const STORAGE_FORMAT_VERSION = 3;

public function __construct(LocalizationScanner $scanner);
public function addFromFile(string $sourceID, string $relativePath, string $languageType, Text $text) : void;
public function addWarning(ParserWarning $warning) : void;
public function getHashes() : StringHash[];
public function hashExists(string $hash) : bool;
public function getHash(string $hash) : StringHash;
public function toArray() : array;
public function fromArray(array $array) : bool;
public function hasWarnings() : bool;
public function countWarnings() : int;
public function getWarnings() : CollectionWarning[];
public function countHashes() : int;
public function countFiles() : int;
public function getHashesBySourceID(string $id) : StringHash[];
public function getHashesByLanguageID(string $languageID) : StringHash[];
```

### `StringHash`

Groups all occurrences of a single translatable string (same English text = same MD5 hash).

```php
public function __construct(StringCollection $collection, string $hash);
public function addString(StringInfo $string) : StringHash;
public function toArray() : array;
public function getStrings() : StringInfo[];
public function countFiles() : int;
public function hasSourceID(string $id) : bool;
public function hasLanguageType(string $type) : bool;
public function getText() : ?Text;
public function getHash() : string;
public function isTranslated() : bool;
public function countStrings() : int;
public function getTranslatedText() : string;
public function getFiles() : string[];
public function getFileNames() : string[];
public function getSearchString() : string;
public function getTextAsString() : string;
```

### `StringInfo`

A single occurrence of a translatable string in a specific file.

```php
public const SERIALIZED_SOURCE_TYPE = 'sourceType';
public const SERIALIZED_SOURCE_ID = 'sourceID';
public const SERIALIZED_TEXT = 'text';
public const SERIALIZED_PROPERTIES = 'properties';
public const PROPERTY_LANGUAGE_TYPE = 'languageType';
public const PROPERTY_RELATIVE_PATH = 'relativePath';

public function __construct(StringCollection $collection, string $sourceID, string $sourceType, Text $text);
public function getHash() : string;
public function getSourceID() : string;
public function setProperty(string $name, string $value) : StringInfo;
public function isFile() : bool;
public function isJavascript() : bool;
public function isPHP() : bool;
public function getSourceFile() : string;
public function getLanguageType() : string;
public function getProperty(string $name) : ?string;
public function getSourceType() : string;
public function getText() : Text;
public function getLine() : int;
public function toArray() : array;
public function getProperties() : array;
public static function fromArray(StringCollection $collection, array $array) : StringInfo;
```

### `CollectionWarning`

Serializable representation of a parser warning within the collection.

```php
public function __construct(array $data);
public function getFile() : string;
public function getLine() : int;
public function getLanguageID() : string;
public function getMessage() : string;
```

---

## Source Subsystem

**Namespace:** `AppLocalize\Localization\Source`

Defines where to find source files and their corresponding translation storage.

### `BaseLocalizationSource` (abstract)

```php
public function __construct(string $alias, string $label, string $group, string $storageFolder);

abstract public function getID() : string;

public function getAlias() : string;
public function getLabel() : string;
public function getGroup() : string;
public function getStorageFolder() : string;
public function scan(LocalizationScanner $scanner) : void;
public function getSourceScanner(LocalizationScanner $scanner) : SourceScanner;
```

### `FolderLocalizationSource`

**Extends:** `BaseLocalizationSource`

The primary source type: a folder of PHP/JS files to scan.

```php
public function __construct(string $alias, string $label, string $group, string $storageFolder, string $sourcesFolder);
public function getID() : string;
public function getSourcesFolder() : string;
public function excludeFolder(string $folder) : FolderLocalizationSource;
public function excludeFolders(array $folders) : FolderLocalizationSource;
public function excludeFiles(array $files) : FolderLocalizationSource;
```

### `SourceScanner`

Scans a single source's files using the parser.

```php
public function __construct(BaseLocalizationSource $source, LocalizationScanner $scanner);
public function getParser() : LocalizationParser;
public function parseFile(string $file) : void;
public function getHashes() : StringHash[];
public function countUntranslated() : int;
```
