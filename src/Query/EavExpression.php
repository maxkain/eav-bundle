<?php

namespace Maxkain\EavBundle\Query;

class EavExpression
{
    public function __construct(
        private string $expression
    ) {
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function setExpression(string $expression): static
    {
        $this->expression = $expression;
        return $this;
    }
}
