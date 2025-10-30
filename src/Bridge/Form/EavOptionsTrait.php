<?php

namespace Maxkain\EavBundle\Bridge\Form;

use Maxkain\EavBundle\Options\EavOptionsInterface;

trait EavOptionsTrait
{
    protected array $options;

    protected function getEavOptions(): EavOptionsInterface
    {
        return $this->options[self::EAV_OPTIONS];
    }

    protected function getValueType(): ?string
    {
        return $this->options[self::VALUE_TYPE];
    }

    protected function getValuesType(): ?string
    {
        return $this->options[self::VALUES_TYPE];
    }

    protected function getValueOptions(): array
    {
        return $this->options[self::VALUE_OPTIONS];
    }

    protected function getValueConstraints(): array
    {
        return $this->options[self::VALUE_CONSTRAINTS];
    }

    protected function isEaAutocomplete(): bool
    {
        return $this->options[self::EA_AUTOCOMPLETE];
    }
}
