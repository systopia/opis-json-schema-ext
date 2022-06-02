# Systopia Opis JSON Schema Extension

This is an extension for [Opis JSON Schema](https://opis.io/json-schema/).
It provides the following additional keywords:

* `$calculate`
* `evaluate`
* `$validations`

The `SystopiaValidator` already provides those keywords. To use them in a
different validator class you might want to use `SystopiaSchemaParser` or
`SystopiaVocabulary`.
