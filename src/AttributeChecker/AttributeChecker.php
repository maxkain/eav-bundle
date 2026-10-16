<?php

namespace Maxkain\EavBundle\AttributeChecker;

use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavEntityInterface;
use Maxkain\EavBundle\Tag\TagChecker;

class AttributeChecker
{
    public function __construct(
        protected TagChecker $tagChecker,
    ) {
    }

    public function check(
        EavEntityInterface $entity,
        EavAttributeInterface $attribute,
        object $options,
    ): bool {
        return $this->tagChecker->check($entity, $attribute, $options);
    }
}
