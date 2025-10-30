<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

interface EavInverterInterface
{
    /**
     * @param iterable<EavInterface> $eavs Collection of Eav entities.
     * @return iterable<EavInterface> Collection of Eav entities.
     */
    public function invert(
        mixed $entity,
        array $items,
        iterable $eavs,
        InverterOptionsInterface|string $options,
        bool $withAddOnly = false
    ): iterable;

    public function isValid(): bool;

    /**
     * @return array<EavInverterViolation>
     */
    public function getViolations(): array;

    public function findAllowedAttributes(mixed $entity, InverterOptionsInterface|string $options): array;
}
