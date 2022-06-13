<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Keywords\PrecisionKeyword;

final class PrecisionKeywordParser extends KeywordParser
{
    public function __construct(string $keyword = 'precision')
    {
        parent::__construct($keyword);
    }

    /**
     * {@inheritDoc}
     */
    public function type(): string
    {
        return static::TYPE_NUMBER;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ParseException
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        if (!$this->keywordExists($info)) {
            return null;
        }

        $value = $this->keywordValue($info);
        if (!$value instanceof \stdClass && !\is_int($value)) {
            throw $this->keywordException('{keyword} must contain an integer', $info);
        }

        return new PrecisionKeyword(Variable::create($value, $parser));
    }
}
