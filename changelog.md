## v1.6.0 - Added new countries (Breaking-S, Deprecation)
- Countries: Added Finland (FI).
- Countries: Added Belgium (BE).
- Countries: Added Singapore (SG). 
- Countries: Added Ireland (IE).
- Countries: Now using dynamic class loading.
- Countries: Renamed and namespaced all country classes.
- Currencies: Now using dynamic class loading.
- Currencies: Renamed and namespaced all currency classes.
- Currencies: Added the Singapore Dollar (SGD).
- Tests: Namespaced and organized all test classes.
- Editor: Better path display for files with warnings.
- Parser: Replaced the obsolete JTokenizer with [Peast](https://github.com/mck89/peast).

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
