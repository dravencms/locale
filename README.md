# Dravencms Locale module

This is a Locale module for dravencms

## Instalation

The best way to install dravencms/locale is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/locale:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	locale: Dravencms\Locale\DI\LocaleExtension
	translation: Kdyby\Translation\DI\TranslationExtension
```
