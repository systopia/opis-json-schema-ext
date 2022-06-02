<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Test\Expression;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Systopia\OpisJsonSchemaExt\Expression\SymfonyExpressionHandler;

/**
 * @covers \Systopia\OpisJsonSchemaExt\Expression\SymfonyExpressionHandler
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
        static::assertTrue(SymfonyExpressionHandler::isAvailable());
    }

    public function testEvaluate(): void
    {
        $this->expressionLanguage->expects(static::once())->method('evaluate')
            ->with('a == 2', ['a' => 2])->willReturn(false);
        static::assertFalse($this->expressionHandler->evaluate('a == 2', ['a' => 2]));
    }

    public function testValidateEvaluationExpressionFail(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('test');
        $this->expressionLanguage->expects(static::once())->method('parse')
            ->with('a == 2', ['a'])->willThrowException(new \Exception('test'));
        $this->expressionHandler->validateEvaluationExpression('a == 2', ['a']);
    }

    public function testValidateEvaluationExpressionSuccess(): void
    {
        $this->expressionLanguage->expects(static::once())->method('parse')
            ->with('a == 2', ['a'])
        ;
        $this->expressionHandler->validateEvaluationExpression('a == 2', ['a']);
    }

    public function testCalculate(): void
    {
        $this->expressionLanguage->expects(static::once())->method('evaluate')
            ->with('a * 2', ['a' => 2])->willReturn(123);
        static::assertSame(123, $this->expressionHandler->calculate('a * 2', ['a' => 2]));
    }

    public function testValidateCalcExpressionFail(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('test');
        $this->expressionLanguage->expects(static::once())->method('parse')
            ->with('a * 2', ['a'])->willThrowException(new \Exception('test'));
        $this->expressionHandler->validateCalcExpression('a * 2', ['a']);
    }

    public function testValidateCalcExpressionSuccess(): void
    {
        $this->expressionLanguage->expects(static::once())->method('parse')
            ->with('a * 2', ['a'])
        ;
        $this->expressionHandler->validateCalcExpression('a * 2', ['a']);
    }

    public function testWithoutMock(): void
    {
        $expressionHandler = new SymfonyExpressionHandler();
        static::assertSame(4, $expressionHandler->calculate('2 * a', ['a' => 2]));
        static::assertTrue($expressionHandler->evaluate('a == 2', ['a' => 2]));
    }
}
