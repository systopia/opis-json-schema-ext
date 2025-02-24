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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Systopia\JsonSchema\Exceptions\CalculationFailedException;
use Systopia\JsonSchema\Expression\SymfonyExpressionHandler;

/**
 * @covers \Systopia\JsonSchema\Expression\SymfonyExpressionHandler
 */
final class SymfonyExpressionHandlerTest extends TestCase
{
    /**
     * @var ExpressionLanguage|MockObject
     */
    private MockObject $expressionLanguage;

    private SymfonyExpressionHandler $expressionHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);
        $this->expressionHandler = new SymfonyExpressionHandler($this->expressionLanguage);
    }

    public function testIsAvailable(): void
    {
        self::assertTrue(SymfonyExpressionHandler::isAvailable());
    }

    public function testEvaluate(): void
    {
        $this->expressionLanguage->expects(self::once())->method('evaluate')
            ->with('a == 2', ['a' => 2])->willReturn(false)
        ;
        self::assertFalse($this->expressionHandler->evaluate('a == 2', ['a' => 2]));
    }

    public function testValidateEvaluationExpressionFail(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('test');
        $this->expressionLanguage->expects(self::once())->method('parse')
            ->with('a == 2', ['a'])->willThrowException(new \Exception('test'))
        ;
        $this->expressionHandler->validateEvaluationExpression('a == 2', ['a']);
    }

    public function testValidateEvaluationExpressionSuccess(): void
    {
        $this->expressionLanguage->expects(self::once())->method('parse')
            ->with('a == 2', ['a'])
        ;
        $this->expressionHandler->validateEvaluationExpression('a == 2', ['a']);
    }

    public function testCalculate(): void
    {
        $this->expressionLanguage->expects(self::once())->method('evaluate')
            ->with('a * 2', ['a' => 2])->willReturn(123)
        ;
        self::assertSame(123, $this->expressionHandler->calculate('a * 2', ['a' => 2]));
    }

    public function testCalculateFail(): void
    {
        $exception = new \Exception('test', 123);
        self::expectExceptionObject(new CalculationFailedException('test', 123, $exception));
        $this->expressionLanguage->expects(self::once())->method('evaluate')
            ->with('a * 2', ['a' => 2])->willThrowException($exception)
        ;
        $this->expressionHandler->calculate('a * 2', ['a' => 2]);
    }

    public function testValidateCalcExpressionFail(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('test');
        $this->expressionLanguage->expects(self::once())->method('parse')
            ->with('a * 2', ['a'])->willThrowException(new \Exception('test'))
        ;
        $this->expressionHandler->validateCalcExpression('a * 2', ['a']);
    }

    public function testValidateCalcExpressionSuccess(): void
    {
        $this->expressionLanguage->expects(self::once())->method('parse')
            ->with('a * 2', ['a'])
        ;
        $this->expressionHandler->validateCalcExpression('a * 2', ['a']);
    }

    public function testWithoutMock(): void
    {
        $expressionHandler = new SymfonyExpressionHandler();
        self::assertSame(4, $expressionHandler->calculate('2 * a', ['a' => 2]));
        self::assertTrue($expressionHandler->evaluate('a == 2', ['a' => 2]));
    }
}
