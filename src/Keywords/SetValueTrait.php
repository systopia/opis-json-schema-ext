<?php

declare(strict_types=1);

namespace Systopia\OpisJsonSchemaExt\Keywords;

use Opis\JsonSchema\ValidationContext;

trait SetValueTrait
{
    /**
     * @param callable(ValidationContext): mixed $transform
     */
    public function setValue(ValidationContext $context, callable $transform): void
    {
        $path = $context->currentDataPath();
        $target = &$this->getDataReference($context, $path);
        $target = $transform($target);
        $this->resetCurrentData($context);
    }

    public function unsetValue(ValidationContext $context): void
    {
        $path = $context->currentDataPath();
        $lastKey = array_pop($path);
        $parentData = &$this->getDataReference($context, $path);

        if (\is_object($parentData)) {
            // @phpstan-ignore-next-line
            unset($parentData->{$lastKey});
        } else {
            unset($parentData[$lastKey]);
        }

        $this->resetCurrentData($context);
    }

    /**
     * @param array<int|string> $path
     *
     * @return mixed
     */
    private function &getDataReference(ValidationContext $context, array $path)
    {
        $data = $context->rootData();
        foreach ($path as $key) {
            if (\is_object($data)) {
                // @phpstan-ignore-next-line
                $data = &$data->{$key};
            } else {
                $data = &$data[$key];
            }
        }

        return $data;
    }

    private function resetCurrentData(ValidationContext $context): void
    {
        $resetPath = [];
        foreach (array_reverse($context->currentDataPath()) as $key) {
            array_unshift($resetPath, $key);
            $context->popDataPath();
            if ('array' !== $context->currentDataType()) {
                break;
            }
        }

        foreach ($resetPath as $key) {
            $context->pushDataPath($key);
        }
    }
}
