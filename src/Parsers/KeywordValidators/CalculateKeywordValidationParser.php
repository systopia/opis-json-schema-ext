<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Parsers\KeywordValidators;

use Opis\JsonSchema\Exceptions\InvalidKeywordException;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\KeywordValidator;
use Opis\JsonSchema\Parsers\KeywordValidatorParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\Expression\Calculation;
use Systopia\JsonSchema\Expression\CalculatorUtil;
use Systopia\JsonSchema\KeywordValidators\CalculateInitKeywordValidator;
use Systopia\JsonSchema\KeywordValidators\CalculateKeywordValidator;

final class CalculateKeywordValidationParser extends KeywordValidatorParser
{
    public function __construct(string $keyword = '$calculate')
    {
        parent::__construct($keyword);
    }

    /**
     * @throws ParseException
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?KeywordValidator
    {
        if (!CalculatorUtil::hasCalculator($parser)) {
            return null;
        }

        if ($this->keywordExists($info)) {
            $calculation = Calculation::parse($this->keywordValue($info), $parser);

            try {
                CalculatorUtil::getCalculator($parser)->validateCalcExpression(
                    $calculation->getExpression(),
                    $calculation->getVariableNames()
                );
            } catch (\Exception $e) {
                throw new InvalidKeywordException(
                    sprintf('Validating calculation failed: %s', $e->getMessage()),
                    $this->keyword,
                    $info
                );
            }

            return new CalculateKeywordValidator($calculation);
        }

        // Calculated values need to be set for the "$calculated" keyword to be evaluated.
        // This is ensured by the CalculateInitKeywordValidator
        if (!$this->keywordExists($info, 'properties')) {
            return null;
        }

        $properties = $this->keywordValue($info, 'properties');

        $calculatedProperties = [];
        foreach ($properties as $name => $value) {
            if (property_exists($value, $this->keyword)) {
                $calculatedProperties[] = $name;
            }
        }

        return [] === $calculatedProperties ? null : new CalculateInitKeywordValidator($calculatedProperties);
    }
}
