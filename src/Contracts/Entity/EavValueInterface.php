<?php

namespace Maxkain\EavBundle\Contracts\Entity;

interface EavValueInterface
{
    public function getId(): mixed;
    public function getTitle(): string;
}
