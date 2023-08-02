<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Schemas;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Schemas\ObjectSchema;
use Opis\JsonSchema\ValidationContext;

/**
 * This implementation allows multiple errors on the same keyword depth.
 *
 * @see https://github.com/opis/json-schema/issues/107
 */
final class MultiErrorObjectSchema extends ObjectSchema
{
    protected function applyKeywords(array $keywords, ValidationContext $context): ?ValidationError
    {
        $errors = [];
        foreach ($keywords as $keyword) {
            if (null !== ($error = $keyword->validate($context, $this))) {
                $errors[] = $error;
            }
        }

        if ([] === $errors) {
            return null;
        }

        if (1 === \count($errors)) {
            return $errors[0];
        }

        /** @var Schema $schema */
        $schema = $context->schema();

        /*
         * message must not contain data as placeholder because \stdClass is not
         * handled by Opis\JsonSchema\Errors\ErrorFormatter (and it probably
         * makes no sense in most cases).
         */
        return new ValidationError(
            'schema',
            $schema,
            DataInfo::fromContext($context),
            'The data does not match the schema',
            ['data' => $context->currentData()],
            $errors
        );
    }
}
