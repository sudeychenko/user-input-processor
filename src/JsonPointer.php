<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

final class JsonPointer extends AbstractPointer
{
    public function getPointer(): string
    {
        $pointer = '#';

        foreach ($this->propertyPath as $pathItem) {
            $pointer .= '/' . $pathItem;
        }

        return $pointer;
    }
}
