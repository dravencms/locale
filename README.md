# DravenCMS Locale

Locale and currency management for multilingual DravenCMS applications. The package connects persisted locale settings with Contributte Translation and provides formatting filters, current-locale resolvers, administration, and a frontend switcher.

## Features

- Active/default locale and currency records.
- Locale-specific date, time, number, and currency formats.
- Current locale and currency resolvers.
- Frontend locale switcher component.
- Latte formatting filters.
- Optional admin screens and ACL fixtures.

## Installation

```bash
composer require dravencms/locale
```

Apply the Doctrine schema and load fixtures so the application has at least one active default locale and currency. `CurrentLocaleResolver` throws when no default locale is available.

Install `dravencms/admin` for management screens and `dravencms/user` for user/ACL integration.

## Localized Presenters

Use `TLocalizedPresenter` in a shared frontend presenter:

```php
use Dravencms\Locale\TLocalizedPresenter;

abstract class FrontPresenter extends \Dravencms\FrontModule\BasePresenter
{
    use TLocalizedPresenter;
}
```

The trait makes `locale` a persistent presenter parameter and assigns the current language code to `$lang` in templates. Route definitions should include the locale parameter when language-prefixed URLs are required.

Inject `CurrentLocaleResolver` or `CurrentCurrencyResolver` in services that need the persisted locale/currency entity rather than only the translator's language code.

## License

This package is licensed under the LGPL-3.0 license.
