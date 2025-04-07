# Systopia Opis JSON Schema Extension

This is an extension for [Opis JSON Schema](https://opis.io/json-schema/).

## Keywords

The following additional keywords are provided:

* `$calculate`
* `evaluate`
* `maxDate`
* `minDate`
* `noIntersect` An array must not contain intersecting intervals.
* `$order` Order arrays. (Only performed, if array has no violations.)
* `precision`
* `$tag` Tagged data can be fetched from a data container after validation.
* `$validations`
* `$limitValidation` Allows to limit validation under specifiable conditions. See [below](#limit-validation).

See [tests](tests/) for how to use them.

The [`SystopiaValidator`](./src/SystopiaValidator.php) already provides those
keywords. To use them in a different validator class you might want to use
[`SystopiaSchemaParser`](./src/Parsers/SystopiaSchemaParser.php) or
[`SystopiaVocabulary`](./src/Parsers/SystopiaVocabulary.php).

### Limit validation

The keyword `$limitValidation` allows to limit validation under specifiable
conditions. The reason behind this keyword was to persist forms in an incomplete
state. This can also be achieved with the `if-then-else` keywords, though it
dramatically increases the size of the schema and is error-prone (at least for
complex schemas).

The structure of the `$limitValidation` keyword is:
```json
{
  "condition": JSON Schema,
  "rules": [
    {
      "keyword": JSON Schema,
      "keywordValue": JSON Schema,
      "value": JSON Schema,
      "calculatedValueUsedViolatedData": boolean|null,
      "validate": boolean
    }
  ],
  "schema": JSON Schema
}
```

`rules` can have an indefinite number of entries.

If the schema at `condition` is matched, limited validation is performed. It is
applied on the keywords at the same depth and the keywords below. If it's not
set, the result of the condition evaluation on a higher level is used. If the
`$limitValidation` keyword is not used at a higher level, `false` is used as
fallback.

The properties of a rule have the following defaults:
* `keyword`, `keywordValue`, `value`: `true`
* `calculatedValueUsedViolatedData`: `null`
* `validate`: `false`

To the entries in `rules` the [default rules](#default-rules) are always
appended. To prevent the execution of the default rules a rule with just the
`validate` property can be used (`{ "validate": false }` or
`{ "validate": true }`).

`schema` has the default value `true`.

If the specified condition is matched, the rules are applied in case of a
violation of a keyword at the same depth or of a keyword below. In case of a
violation it will be iterated over the rules until a matching rule is found. If
the `validate` property of that rule is false the violation will be ignored.

The rule matching is done like this:

* The violated keyword (e.g. `type`) is matched against the schema in `keyword`.
* The value of the violated keyword is matched against the schema in `keywordValue`.
* The invalid value is matched against the schema in `value`.

All schemas must be matched for a rule to be matched. If
`calculatedValueUsedViolatedData` is not `null`, the value has to be calculated
(with the `$calculate` keyword) and must or must not have used violated data
depending on the actual value of `calculatedValueUsedViolatedData`. Violated
data is used, if the calculation references a value that has a validation error
(including ignored ones).

The keyword `schema` allows to specify a schema that is validated additionally,
if the condition is matched. This allows for example to require some properties
on limited validation: `"schema": { "required": ["foo", "bar"] }`.

Example:
```json
{
  "$limitValidation": {
    "condition": {
      "properties": {
        "action": { "const": "save" }
      }
    },
    "rules": [
      {
        "keyword": { "const": "type" },
        "keywordValue": { "not": { "const": "string" } },
        "value": { "type": ["number", "bool"] },
        "validate": true
      },
      {
        "validate": false
      }
    ],
    "schema": {
      "required": ["example"]
    }
  }
}
```

This means that if the property `action` is set to `"save"`, limited validation
is applied. The rules say that only violations are treated as such, if these
conditions are met:

* The violated keyword is `type`.
* The value of the violated keyword is not `"string"`.
* The validated value is neither a number nor a boolean.

All other violations are ignored because of the second rule.

Additionally, the property `example` is required, if `action` is `"save"`.

#### Default rules

The default rules are:

```json
[
  {
    "value": { "const": null }
  },
  {
    "keyword": { "not": { "const": "type" } },
    "value": { "enum": [false, ""] }
  },
  {
    "keyword": {
      "enum": [
        "minLength",
        "minItems",
        "minContains",
        "minProperties",
        "required",
        "dependentRequired"
      ]
    }
  },
  {
    "calculatedValueUsedViolatedData": true
  },
  {
    "validate": true
  }
]
```

The rules mean:

1. No violation error, if the validated value is `null`.
2. No violation error, if the violated keyword is not `type` and the validated value is `false` or `""` (empty string).
3. No violation error, if the validated keyword is one of:
   `minLength`, `minItems`, `minContains`, `minProperties`, `required`, `dependentRequired`
4. No violation error, if value is calculated and calculation used data with violations (including ignored ones).
5. Every other validation without limitation.

## Empty array to object conversion

If the option `convertEmptyArrays` is set to `true` (disabled by default), empty
arrays will be  converted to objects if the schema type contains `object`, but
not `array`. This might be necessary if the data to validate was already
decoded.

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
