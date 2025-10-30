<?php

namespace Maxkain\EavBundle\Contracts\Entity\Tag;

use Maxkain\EavBundle\Contracts\Entity\EavEntityInterface;

interface EavEntityWithTagsInterface extends EavEntityInterface
{
    /**
     * @return iterable<EavTagInterface>
     */
    public function getEavTags(string $tagFqcn): iterable;
}
