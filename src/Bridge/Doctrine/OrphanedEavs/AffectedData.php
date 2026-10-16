<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeWithTagsInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavEntityWithTagsInterface;

class AffectedData
{
    /**
     * @var array<EavAttributeWithTagsInterface|scalar>
     */
    public array $attributes = [];

    /**
     * @var array<EavEntityWithTagsInterface|scalar>
     */
    public array $entities = [];
}
