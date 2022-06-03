<?php

declare(strict_types=1);

namespace Systopia\JsonSchema\Expression\Variables;

use Assert\Assertion;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;
use Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException;
use Systopia\JsonSchema\Exceptions\VariableResolveException;

final class JsonPointerVariable extends Variable
{
    private JsonPointer $pointer;

    /**
     * @var null|mixed
     */
    private $fallback;

    /**
     * @param null|mixed $fallback
     */
    public function __construct(JsonPointer $pointer, $fallback = null)
    {
        $this->pointer = $pointer;
        $this->fallback = $fallback;
    }

    public static function isAllowed(SchemaParser $parser): bool
    {
        return true === $parser->option('allowDataKeyword');
    }

    /**
     * @throws ParseException
     */
    public static function parse(\stdClass $data, SchemaParser $parser): self
    {
        if (!self::isAllowed($parser)) {
            throw new ParseException('keyword "$data" is not allowed');
        }

        if (property_exists($data, 'fallback') && null === $data->fallback) {
            throw new ParseException('fallback must not be null');
        }

        if (!property_exists($data, '$data')) {
            throw new ParseException('keyword "$data" is required');
        }

        $pointer = JsonPointer::parse($data->{'$data'});
        if (null === $pointer) {
            throw new ParseException(sprintf('Invalid JSON pointer "%s"', $data->{'$data'}));
        }

        return new self($pointer, $data->fallback ?? null);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(ValidationContext $context, int $flags = 0)
    {
        if (0 !== ($flags & self::FLAG_FAIL_ON_VIOLATION)) {
            $path = $this->pointer->absolutePath($context->fullDataPath());
            Assertion::notNull($path);

            if (ErrorCollectorUtil::getErrorCollector($context)->hasErrorAt($path)) {
                throw new ReferencedDataHasViolationException(
                    sprintf('The property at path "%s" has violations', JsonPointer::pathToString($path))
                );
            }
        }

        $value = $this->pointer->data($context->rootData(), $context->currentDataPath()) ?? $this->fallback;
        if (null === $value && 0 !== ($flags & Variable::FLAG_FAIL_ON_UNRESOLVED)) {
            $path = $this->pointer->absolutePath($context->fullDataPath());
            Assertion::notNull($path);

            throw new VariableResolveException(
                sprintf('The property at path "%s" could not be resolved', JsonPointer::pathToString($path))
            );
        }

        return $value;
    }
}
