<?php

namespace Maxkain\EavBundle\Options;

interface TagOptionsInterface
{
    public function getTagFqcn(): ?string;
    public function getTagKey(): ?string;
    public function getAttributeTagFqcn(): ?string;
    public function isMultipleTags(): bool;
}
