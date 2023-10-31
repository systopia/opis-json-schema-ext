# Systopia Opis JSON Schema Extension

This is an extension for [Opis JSON Schema](https://opis.io/json-schema/).

## Keywords

The following additional keywords are provided:

* `$calculate`
* `evaluate`
* `maxDate`
* `minDate`
* `precision`
* `$validations`

The [`SystopiaValidator`](./src/SystopiaValidator.php) already provides those
keywords. To use them in a different validator class you might want to use
[`SystopiaSchemaParser`](./src/Parsers/SystopiaSchemaParser.php) or
[`SystopiaVocabulary`](./src/Parsers/SystopiaVocabulary.php).

## Translation

This extension allows to translate `ValidationError`s:

First create an instance of `TranslatorInterface`:

```php
$translator = new Translator($locale, $messages);
```

If there is a localisation in the `messages` directory you can use:

```php
$translator = TranslatorFactory::createTranslator($locale);
```

Then create an instance of `ErrorTranslator`:

```php
$errorTranslator = new ErrorTranslator($translator);
```

Let the `ErrorTranslator` translate a validation error:

```php
echo $errorTranslator->trans($error);
```
