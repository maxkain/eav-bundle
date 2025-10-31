<?php

namespace Maxkain\EavBundle\Query;

class EavComparison
{
    public function __construct(
        private string $operator,
        private mixed $value
    ) {
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }
}
