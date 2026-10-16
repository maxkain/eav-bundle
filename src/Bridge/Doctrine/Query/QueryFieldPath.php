<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

class QueryFieldPath
{
    public function __construct(
        protected readonly string $fieldPath,
    ) {
    }

    public function __toString(): string
    {
        return $this->fieldPath;
    }
}
