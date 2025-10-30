<?php

namespace Maxkain\EavBundle\Options;

interface TagPropertyMappingInterface
{
    public function getEntityTag(): string;
    public function getEntityTags(): string;
    public function getAttributeTagAttribute(): string;
    public function getAttributeTagTag(): string;
    public function getAttributeForAllTags(): string;
}
