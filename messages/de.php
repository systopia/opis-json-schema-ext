<?php
declare(strict_types=1);

return [
    'additionalItems' => 'Alle zusätzlichen Einträge müssen zum Schema passen.',
    'additionalItems.notAllowed' => 'Zusätzliche Einträge sind nicht erlaubt.',
    'additionalProperties' => 'Alle zusätzlichen Eigenschaften müssen zum Schema passen: {properties}',
    'additionalProperties.notAllowed' => 'Zusätzliche Eigenschaften sind nicht erlaubt: {properties}',
    'allOf' => 'Die Daten sind nicht gültig.',
    'anyOf' => 'Die Daten sind nicht gültig.',
    'const' => 'Der Wert muss "{const}" sein.',
    'contains.false' => 'Es sind keine Einträge erlaubt.',
    'contains.true' => 'Es ist mindestens ein Eintrag erforderlich.',
    'contains' => 'Mindestens ein Eintrag muss zum Schema passen.',
    'minContains' => '{min, plural,
        =1 {Es ist mindestens ein Eintrag erforderlich.}
        other {Es sind mindestens # Einträge erforderlich.}
    }',
    'minContains.schema' => '{min, plural,
        =1 {Mindestens ein Eintrag muss zum Schema passen.}
        other {Mindestens # Einträge müssen zum Schema passen.}
    }',
    'maxContains' => '{max, plural,
        =0 {Es sind keine Einträge erlaubt.}
        =1 {Es ist nicht mehr als ein Eintrag erlaubt.}
        other {Es sind nicht mehr als # Einträge erlaubt.}
    }',
    'maxContains.schema' => '{max, plural,
        =0 {Es darf kein Eintrag zum Schema passen.}
        =1 {Nicht mehr als ein Eintrag darf zum Schema passen.}
        other {Nicht mehr als # Einträge dürfen zum Schema passen.}
    }',
    'contentEncoding' => 'Die Daten müssen als "{encoding}" codiert sein.',
    'contentMediaType' => 'Der Datentyp muss "{media}" sein.',
    'contentSchema.json' => 'Ungültiger JSON-Inhalt: {message}',
    'contentSchema' => 'Die Daten sind nicht gültig.',
    'dependencies' => 'Die Daten müssen zum Schema passen, dass für die Eigenschaft "{property}" definiert ist.',
    'dependencies.missing' => 'Die Eigenschaft "{missing}" wird von der Eigenschaft "{property}" benötigt.',
    'dependencies.notAllowed' => 'Die Eigenschaft "{property}" ist nicht erlaubt.',
    'dependentRequired' => 'Die Eigenschaft "{missing}" wird von der Eigenschaft "{property}" benötigt.',
    'dependentSchemas' => 'Die Daten passen nicht zum Abhängigkeitsschema, das für die Eigenschaft "{property}" definiert ist.',
    'dependentSchemas.notAllowed' => 'Die Eigenschaft "{property}" ist nicht erlaubt.',
    'else' => 'Die Daten sind nicht gültig.',
    'enum' => 'Der Wert ist nicht erlaubt.',
    'exclusiveMaximum' => 'Der Wert muss kleiner als {max} sein.',
    'exclusiveMinimum' => 'Der Wert muss größer als {min} sein.',
    'format' => 'Die Daten sind nicht vom Format "{format}".',
    'items' => 'Alle Einträge müssen zum Schema passen.',
    'items.false' => 'Es sind keine Einträge erlaubt.',
    'items.notAllowed' => 'Ein Eintrag an Index {index} ist nicht erlaubt.',
    'maximum' => 'Der Wert muss kleiner oder gleich {max} sein.',
    'maxItems' => '{max, plural,
        =0 {Es sind keine Einträge erlaubt.}
        =1 {Es ist nur ein Eintrag erlaubt.}
        other {Es sind nicht mehr als # Einträge erlaubt.}
    }',
    'maxLength' => 'Der Wert darf nicht länger als {max} Zeichen sein (aktuell: {length}).',
    'maxProperties' => '{max, plural,
        =0 {Es sind keine Eigenschaften erlaubt.}
        =1 {Es ist nur eine Eigenschaft erlaubt.}
        other {Es sind nicht mehr als # Eigenschaften erlaubt.}
    }',
    'minimum' => 'Der Wert muss größer oder gleich {min} sein.',
    'minItems' => '{min, plural,
        =1 {Es ist mindestens ein Eintrag erforderlich.}
        other {Es sind mindestens # Einträge erforderlich.}
    }',
    'minLength' => 'Der Wert muss mindestens {min} Zeichen lang sein (aktuell: {length}).',
    'minProperties' => '{min, plural,
        =1 {Es ist mindestens ein Eintrag erforderlich.}
        other {Es sind mindestens # Einträge erforderlich.}
    }',
    'multipleOf' => 'Die Zahl muss ein Vielfaches von {divisor} sein.',
    'not' => 'Die Daten sind nicht gültig.',
    'not.notAllowed' => 'Die Daten sind nicht gültig.',
    'oneOf' => 'Die Daten sind nicht gültig.',
    'pattern' => 'Der Wert ist nicht gültig.',
    'patternProperties' => 'Eigenschaften, die zum Muster "{pattern}" passen, müssen auch zum zugehörigen Schema passen.',
    'patternProperties.notAllowed' => 'Folgende Eigenschaften sind nicht erlaubt: {forbidden}.',
    'properties' => 'Diese Eigenschaften passen nicht zum Schema: {properties}.',
    'properties.notAllowed' => 'Die Eigenschaft "{property}" ist nicht erlaubt.',
    'propertyNames' => 'Die Eigenschaft "{property}" passt nicht zum Schema.',
    'propertyNames.notAllowed' => 'Es sind keine Eigenschaften erlaubt.',
    'required' => 'Folgende Eigenschaften fehlen: {missing}.',
    'then' => 'Die Daten passen sind nicht gültig.',
    'type' => 'Der Datentyp "{type}" entspricht nicht dem erwarteten Typ "{expected}".',
    'unevaluatedItems' => 'Folgende nicht evaluierte Einträge sind ungültig: {indexes}.',
    'unevaluatedItems.notAllowed' => 'Nicht evaluierte Einträge sind nicht erlaubt: {indexes}.',
    'unevaluatedProperties' => 'Folgende nicht evaluierte Eigenschaften sind ungültig: {properties}.',
    'unevaluatedProperties.notAllowed' => 'Nicht evaluierte Eigenschaften sind nicht erlaubt: {properties}.',
    'uniqueItems' => 'Jeder Eintrag darf nur genau einmal vorkommen.',

    'evaluate' => 'Die Auswertung einer Berechnung war nicht erfolgreich.',
    'evaluate.resolve' => 'Die Auswertung einer Berechnung war nicht möglich, da nicht alle Variablen aufgelöst werden konnten.',
    'maxDate' => 'Das Datum darf nicht nach dem {maxDateTimestamp, date} sein.',
    'minDate' => 'Das Datum darf nicht vor dem {minDateTimestamp, date} sein.',
    'noIntersect' => 'Die Intervalle dürfen sich nicht überschneiden.',
    'precision' => '{precision, plural, {
        =1 {Die Zahl darf nicht mehr als eine Dezimalstelle haben.}
        other {Die Zahl darf nicht mehr als # Dezimalstellen haben.}
    }',

    '$calculate.required.unresolved' => 'Der Wert wird benötigt, aber konnte nicht ermittelt werden aufgrund von nicht aufgelösten Variablen.',

    '_invalidData' => 'Ungültiger Wert für Schlüsselwort "{keyword}".',
    '_resolveFailed' => 'Auflösen des Werts für Schlüsselwort "{keyword}" ist fehlgeschlagen.',
    '_invalidKeywordValue' => 'Ungültiger Wert für Schlüsselwort "{keyword}": {value}',
];
