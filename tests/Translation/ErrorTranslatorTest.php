<?php

/*
 * Copyright 2023 SYSTOPIA GmbH
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

declare(strict_types=1);

namespace Systopia\JsonSchema\Test\Translation;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Translation\ErrorTranslator;
use Systopia\JsonSchema\Translation\TranslatorInterface;

/**
 * @covers \Systopia\JsonSchema\Translation\ErrorTranslator
 */
final class ErrorTranslatorTest extends TestCase
{
    /**
     * @var MockObject&TranslatorInterface
     */
    private MockObject $translatorMock;
    private ErrorTranslator $errorTranslator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translatorMock = $this->createMock(TranslatorInterface::class);
        $this->errorTranslator = new ErrorTranslator($this->translatorMock);
    }

    public function test(): void
    {
        $schema = new EmptySchema(new SchemaInfo(false, null));
        $dataInfo = new DataInfo(null, null, null);
        $error = new ValidationError('minimum', $schema, $dataInfo, 'message', ['min' => 2]);

        $this->translatorMock->expects(self::once())->method('trans')
            ->with('minimum', ['min' => 2, 'keyword' => 'minimum'])
            ->willReturn('translation')
        ;

        self::assertSame('translation', $this->errorTranslator->trans($error));
    }

    public function testNoTranslation(): void
    {
        $schema = new EmptySchema(new SchemaInfo(false, null));
        $dataInfo = new DataInfo(null, null, null);
        $error = new ValidationError('minimum', $schema, $dataInfo, 'minimum: {min}', ['min' => 2]);

        $this->translatorMock->expects(self::once())->method('trans')
            ->with('minimum', ['min' => 2, 'keyword' => 'minimum'])
            ->willReturn('minimum')
        ;

        self::assertSame('minimum: 2', $this->errorTranslator->trans($error));
    }

    public function testAlreadyTranslated(): void
    {
        $schema = new EmptySchema(new SchemaInfo(false, null));
        $dataInfo = new DataInfo(null, null, null);
        $error = new ValidationError('minimum', $schema, $dataInfo, 'minimum: {min}', ['min' => 2, '__translated' => true]);

        $this->translatorMock->expects(self::never())->method('trans');

        self::assertSame('minimum: 2', $this->errorTranslator->trans($error));
    }
}
