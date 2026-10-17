<?php

namespace Maxkain\EavBundle\Contracts\Entity\Tag;

use Maxkain\EavBundle\Contracts\Entity\EavEntityInterface;

interface EavEntityWithTagsInterface extends EavEntityInterface
{
    /**
     * @return EavTagInterface|iterable<EavTagInterface>|null
     */
    public function getEavTags(string $tagKey): EavTagInterface|iterable|null;
}
