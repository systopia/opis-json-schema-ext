<?php
declare(strict_types=1);

return [
    'additionalItems' => 'All additional items must match the schema.',
    'additionalItems.notAllowed' => 'Additional items are not allowed.',
    'additionalProperties' => 'All additional properties must match schema: {properties}',
    'additionalProperties.notAllowed' => 'Additional properties are not allowed: {properties}',
    'allOf' => 'The data is not valid.',
    'anyOf' => 'The data is not valid.',
    'const' => 'The value must be "{const}".',
    'const.false' => 'The field must not be checked.',
    'const.true' => 'The field must be checked.',
    'contains.false' => 'No items are allowed.',
    'contains.true' => 'At least one item is required.',
    'contains' => 'At least one items must match the schema.',
    'minContains' => '{min, plural,
        =1 {At least one item is required.}
        other {At least # items are required.}
    }',
    'minContains.schema' => '{min, plural,
        =1 {At least one item must match the schema.}
        other {At least # items must match the schema.}
    }',
    'maxContains' => '{max, plural,
        =0 {No items are allowed.}
        =1 {Only one item is allowed.}
        other {More than # items are not allowed.}
    }',
    'maxContains.schema' => '{max, plural,
        =0 {No items must match the schema.}
        =1 {Not more than one item must match the schema.}
        other {Not more than # items must match the schema.}
    }',
    'contentEncoding' => 'The data must be encoded as "{encoding}".',
    'contentMediaType' => 'The type of the data must be "{media}".',
    'contentSchema.json' => 'Invalid JSON content: {message}',
    'contentSchema' => 'The data is not valid.',
    'dependencies' => 'The data must match the schema that is defined for the property "{property}".',
    'dependencies.missing' => 'The property "{missing}" is required by the property "{property}".',
    'dependencies.notAllowed' => 'The property "{property}" is not allowed.',
    'dependentRequired' => 'The property "{missing}" is required by the property "{property}".',
    'dependentSchemas' => 'The data does not match the dependent schema that is defined for the property "{property}".',
    'dependentSchemas.notAllowed' => 'The property "{property}" is not allowed.',
    'else' => 'The data is not valid.',
    'enum' => 'The value is not allowed.',
    'exclusiveMaximum' => 'The value must be less than {max}.',
    'exclusiveMinimum' => 'The value must be greater than {min}.',
    'format' => 'The data is not of format "{format}".',
    'items' => 'All entries must match the schema.',
    'items.false' => 'No items are allowed.',
    'items.notAllowed' => 'An item at index {index} is not allowed.',
    'maximum' => 'The value must be less or equal {max}.',
    'maxItems' => '{max, plural,
        =0 {No items are allowed.}
        =1 {Only one item is allowed.}
        other {More than # items are not allowed.}
    }',
    'maxLength' => 'The value must not be longer than {max} characters (currently: {length}).',
    'maxProperties' => '{max, plural,
        =0 {No properties are allowed.}
        =1 {Only one property is allowed.}
        other {More than # properties are not allowed.}
    }',
    'minimum' => 'The value must be greater or equal {min}.',
    'minItems' => '{min, plural,
        =1 {At least one item is required.}
        other {At least # items are required.}
    }',
    'minLength' => '{min, plural,
        =1 {The value is required.}
        other {The value must be at least # characters long (currently: {length}).}
    }',
    'minProperties' => '{min, plural,
        =1 {At least one property is required.}
        other {At least # properties are required.}
    }',
    'multipleOf' => 'The number must be a multiple of {divisor}.',
    'not' => 'The data is not valid.',
    'not.notAllowed' => 'The data is not valid.',
    'oneOf' => 'The data is not valid.',
    'pattern' => 'The value is not valid.',
    'patternProperties' => 'Properties that math the pattern "{pattern}" must also match the related schema.',
    'patternProperties.notAllowed' => 'The following properties are not allowed: {forbidden}.',
    'properties' => 'These properties do not match the schema: {properties}.',
    'properties.notAllowed' => 'The property "{property}" is not allowed.',
    'propertyNames' => 'The property "{property}" does not match the schema.',
    'propertyNames.notAllowed' => 'No properties are allowed.',
    'required' => 'The following properties are missing: {missing}.',
    'then' => 'The data is not valid.',
    'type' => '{type, select,
        null {The value is required.}
        other {The data type "{type}" does not match the expected type "{expected}".}
    }',
    'unevaluatedItems' => 'The following not evaluated items are not valid: {indexes}.',
    'unevaluatedItems.notAllowed' => 'Not evaluated items are not allowed: {indexes}.',
    'unevaluatedProperties' => 'The following not evaluated properties are not valid: {properties}.',
    'unevaluatedProperties.notAllowed' => 'Not evaluated properties are not allowed: {properties}.',
    'uniqueItems' => 'Every item may occur only once.',

    'evaluate' => 'The evaluation of an expression was not successful.',
    'evaluate.resolve' => 'The evaluation of an expression was not possible because not all variables could be resolved.',
    'maxDate' => 'The date must not be after {maxDateTimestamp, date}.',
    'minDate' => 'The date must not be before {minDateTimestamp, date}.',
    'noIntersect' => 'The intervals must not intersect.',
    'precision' => '{precision, plural, {
        =1 {The number must not have more than one decimal.}
        other {The number must not have more than # decimals.}
    }',

    '$calculate.required' => 'The value is required, but could not be determined because of invalid data or unresolved variables.',

    '_invalidData' => 'Invalid value for keyword "{keyword}".',
    '_resolveFailed' => 'Resolving the value for keyword "{keyword}" failed.',
    '_invalidKeywordValue' => 'Invalid value for keyword "{keyword}": {value}',
];
