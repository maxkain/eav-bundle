<?php

namespace Maxkain\EavBundle\Contracts\Entity;

interface EavAttributeInterface
{
    public function getId(): mixed;
    public function getName(): string;
    public function setName(string $name): mixed;
}
