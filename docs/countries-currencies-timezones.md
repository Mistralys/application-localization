# Countries, Currencies & Time Zones

See the [supported countries and locales](overview.md) for a full list of what the
package includes.

## Countries

To work with countries, use the factory method:

```php
use AppLocalize\Localization;
use AppLocalize\Localization\Country\CountryES;

$countries = Localization::createCountries();

// Get a country by its two-letter ISO code
$germany = $countries->getByISO('de');

// Every country has a constant for its ISO code
$spain = $countries->getByISO(CountryES::ISO_CODE);

// Or use the predefined list via choose()
$france = $countries->choose()->fr();
```

## Currencies

To work with currencies, use the factory method:

```php
use AppLocalize\Localization;
use AppLocalize\Localization\Currency\CurrencyEUR;

$currencies = Localization::createCurrencies();

// Get a currency by its three-letter ISO code
$dollar = $currencies->getByISO('USD');

// Every currency has a constant for its ISO code
$euro = $currencies->getByISO(CurrencyEUR::ISO_CODE);

// Or use the predefined list via choose()
$pound = $currencies->choose()->gbp();
```

### Country-specific currency formatting

A currency retrieved from a country offers formatting adjusted to that country's
conventions:

```php
use AppLocalize\Localization;

$eurDE = Localization::createCountries()
    ->choose()
    ->de()
    ->getCurrency();

echo $eurDE->makeReadable(1445.42);
```

Output:

```
1.445,42 €
```

## Time Zones

All supported countries have a time zone associated with them:

```php
use AppLocalize\Localization;

$timeZone = Localization::createCountries()
    ->choose()
    ->de()
    ->getTimezone();

echo $timeZone->getID();            // Europe/Berlin
echo $timeZone->getZoneLabel();     // Europe
echo $timeZone->getLocationLabel(); // Berlin
echo $timeZone->getLabel();         // Europa/Berlin (in the de_DE locale)
```

### Countries with multiple time zones

For countries with multiple time zones, a historically established project default
is returned:

| Country | Default time zone |
|---------|-------------------|
| US | US/Eastern |
| CA | America/Vancouver |
