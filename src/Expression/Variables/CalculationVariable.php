<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Expression\Variables;

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\OpisJsonSchemaExt\Exceptions\ReferencedDataHasViolationException;
use Systopia\OpisJsonSchemaExt\Exceptions\VariableResolveException;
use Systopia\OpisJsonSchemaExt\Expression\Calculation;
use Systopia\OpisJsonSchemaExt\Expression\CalculatorUtil;

final class CalculationVariable extends Variable
{
    private Calculation $calculation;

    /**
     * @var null|mixed
     */
    private $fallback;

    /**
     * @param null|mixed $fallback
     */
    public function __construct(Calculation $calculation, $fallback = null)
    {
        $this->calculation = $calculation;
        $this->fallback = $fallback;
    }

    public static function isAllowed(SchemaParser $parser): bool
    {
        return CalculatorUtil::hasCalculator($parser);
    }

    /**
     * @throws ParseException
     */
    public static function parse(\stdClass $data, SchemaParser $parser): self
    {
        if (!self::isAllowed($parser)) {
            throw new ParseException('Parser option "calculator" is not set');
        }

        if (property_exists($data, 'fallback') && null === $data->fallback) {
            throw new ParseException('fallback must not be null');
        }

        if (!property_exists($data, '$calculate')) {
            throw new ParseException('keyword "$calculate" is required');
        }

        $calculation = Calculation::parse($data->{'$calculate'}, $parser);

        try {
            CalculatorUtil::getCalculator($parser)->validateCalcExpression(
                $calculation->getExpression(),
                $calculation->getVariableNames()
            );
        } catch (\Exception $e) {
            throw new ParseException(sprintf('Validating calculation expression failed: %s', $e->getMessage()));
        }

        return new self($calculation, $data->fallback ?? null);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(ValidationContext $context, int $flags = 0)
    {
        $fallback = $this->calculation->getFallback() ?? $this->fallback;
        if (null === $fallback) {
            $variables = $this->calculation->getVariables(
                $context,
                $flags | Variable::FLAG_FAIL_ON_UNRESOLVED
            );
        } else {
            try {
                $variables = $this->calculation->getVariables(
                    $context,
                    $flags | Variable::FLAG_FAIL_ON_UNRESOLVED
                );
            } catch (VariableResolveException|ReferencedDataHasViolationException $e) {
                return $fallback;
            }
        }

        $calculator = CalculatorUtil::getCalculatorFromContext($context);

        return $calculator->calculate(
            $this->calculation->getExpression(),
            $variables,
        ) ?? $fallback;
    }
}
