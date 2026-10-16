<?php

namespace Maxkain\EavBundle\Tag\Options;

interface EavOptionsWithTagsInterface
{
    /**
     * @return array<TagOptionsInterface>
     */
    public function getTagOptions(): array;
    public function isOrTagLogic(): bool;
}
