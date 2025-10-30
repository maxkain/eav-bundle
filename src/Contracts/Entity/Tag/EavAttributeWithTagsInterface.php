<?php

namespace Maxkain\EavBundle\Contracts\Entity\Tag;

use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;

interface EavAttributeWithTagsInterface extends EavAttributeInterface
{
    public function isForAllEavTags(string $tagFqcn): bool;

    /**
     * @return iterable<EavAttributeTagInterface>
     */
    public function getEavTags(string $tagFqcn): iterable;
}
