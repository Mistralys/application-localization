## v1.6.0 - Namespacing and new countries (Breaking-S, Deprecation)
- Countries: Added Finland (FI).
- Countries: Added Belgium (BE).
- Countries: Added Singapore (SG). 
- Countries: Added Ireland (IE).
- Countries: Added `getLabelInvariant()` to get the label in invariant locale.
- Countries: Now using dynamic class loading.
- Countries: Renamed and namespaced all country classes.
- Countries: Added `getMainLocale()`.
- Currencies: Now using dynamic class loading.
- Currencies: Renamed and namespaced all currency classes.
- Currencies: Added the Singapore Dollar (SGD).
- Locales: Renamed and namespaced all locale classes.
- Locales: Added `getLabelInvariant()` to get the label in the invariant locale.
- Locales: Normalized the labels that were not consistently showing the country name.
- Tests: Namespaced and organized all test classes.
- Editor: Better path display for files with warnings.
- Parser: Replaced the obsolete JTokenizer with [Peast](https://github.com/mck89/peast).
- Dependencies: Updated AppUtils Core to [v2.3.11](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.11).
- Dependencies: Updated AppUtils Collections to [v1.1.5](https://github.com/Mistralys/application-utils-collections/releases/tag/1.1.5).

### Breaking changes

No breaking changes to the class APIs, but a hard requirement
is now to configure a cache folder for the dynamic class loading.
See the [README](./README.md) file for details.

### Deprecations

#### Countries

All country classes have been renamed and namespaced from
`Localization_Country_*` to `Localization\Country\Country*`. 
The old classes are still available, but deprecated. Existing
instance checks will still work even with the new classes
until the deprecated classes are phased out.

#### Currencies

All currency classes have been renamed and namespaced from
`Localization_Currency_*` to `Localization\Currency\Currency*`.
The old classes are still available, but deprecated. Existing
instance checks will still work even with the new classes
until the deprecated classes are phased out.

-----

Older change log entries can be found under `docs/changelog-history`.
