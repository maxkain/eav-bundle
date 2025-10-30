<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeWithTagsInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavEntityWithTagsInterface;

class AffectedData
{
    /**
     * @var array<EavAttributeWithTagsInterface>
     */
    public array $attributes = [];

    /**
     * @var array<EavEntityWithTagsInterface>
     */
    public array $entities = [];
}
