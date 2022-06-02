<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Parsers\Keywords;

use Assert\Assertion;
use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\OpisJsonSchemaExt\Expression\Evaluation;
use Systopia\OpisJsonSchemaExt\Expression\EvaluatorInterface;
use Systopia\OpisJsonSchemaExt\Keywords\EvaluateKeyword;

final class EvaluateKeywordParser extends KeywordParser
{
    public function __construct(string $keyword = 'evaluate')
    {
        parent::__construct($keyword);
    }

    public function type(): string
    {
        return self::TYPE_APPEND;
    }

    /**
     * @throws ParseException
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?EvaluateKeyword
    {
        if (!$this->keywordExists($info)) {
            return null;
        }

        if (null === $evaluator = $parser->option('evaluator')) {
            return null;
        }
        Assertion::isInstanceOf($evaluator, EvaluatorInterface::class);

        $value = $this->keywordValue($info);
        $evaluation = Evaluation::parse($value, $parser);

        try {
            $evaluator->validateEvaluationExpression(
                $evaluation->getExpression(),
                array_merge(['data'], $evaluation->getVariableNames())
            );
        } catch (\Exception $e) {
            throw new InvalidKeywordException(
                sprintf('Validating evaluation expression failed: %s', $e->getMessage()),
                $this->keyword,
                $info
            );
        }

        return new EvaluateKeyword($evaluator, $evaluation);
    }
}
