# Systopia Opis JSON Schema Extension

This is an extension for [Opis JSON Schema](https://opis.io/json-schema/).
It provides the following additional keywords:

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
