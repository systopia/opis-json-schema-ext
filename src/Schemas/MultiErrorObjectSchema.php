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

        return new ValidationError(
            '',
            $schema,
            DataInfo::fromContext($context),
            'Data must match schema',
            [],
            $errors
        );
    }
}
