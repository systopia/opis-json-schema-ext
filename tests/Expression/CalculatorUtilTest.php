<?php

/*
 * Copyright 2022 SYSTOPIA GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\JsonSchema\Test\Expression;

use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\ValidationContext;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Expression\CalculatorInterface;
use Systopia\JsonSchema\Expression\CalculatorUtil;

/**
 * @covers \Systopia\JsonSchema\Expression\CalculatorUtil
 */
final class CalculatorUtilTest extends TestCase
{
    public function testWithCalculator(): void
    {
        $calculator = $this->createMock(CalculatorInterface::class);
        $parser = new SchemaParser([], ['calculator' => $calculator]);
        self::assertTrue(CalculatorUtil::hasCalculator($parser));
        self::assertSame($calculator, CalculatorUtil::getCalculator($parser));

        $context = new ValidationContext('', new SchemaLoader($parser));
        self::assertTrue(CalculatorUtil::hasCalculatorInContext($context));
        self::assertSame($calculator, CalculatorUtil::getCalculatorFromContext($context));
    }

    public function testWithoutCalculator(): void
    {
        $parser = new SchemaParser();
        self::assertFalse(CalculatorUtil::hasCalculator($parser));

        $context = new ValidationContext('', new SchemaLoader($parser));
        self::assertFalse(CalculatorUtil::hasCalculatorInContext($context));
    }
}
