<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Parsers;

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\KeywordParserTrait;

trait EnsurePropertyTrait
{
    use KeywordParserTrait;

    /**
     * @throws InvalidKeywordException
     */
    protected function assertPropertyExists(
        \stdClass $data,
        string $property,
        SchemaInfo $info,
        string $keyword = null
    ): void {
        if (!property_exists($data, $property)) {
            throw $this->keywordException(
                sprintf('{keyword} entries must contain property "%s"', $property),
                $info,
                $keyword
            );
        }
    }
}
