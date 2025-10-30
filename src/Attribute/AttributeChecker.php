<?php

namespace Maxkain\EavBundle\Attribute;

use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeWithTagsInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavEntityWithTagsInterface;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavEntityInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;
use Maxkain\EavBundle\Options\TagOptionsInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;

class AttributeChecker
{
    public function __construct(
        protected EavOptionsRegistry $optionsRegistry
    ) {
    }

    public function check(
        EavEntityInterface $entity,
        EavAttributeInterface $attribute,
    ): bool {
        $options = $this->resolveOptions($entity, $attribute);

        foreach ($options as $option) {
            if ($this->checkOne($entity, $attribute, $option)) {
                return true;
            }
        }

        return false;
    }

    public function checkOne(
        EavEntityInterface $entity,
        EavAttributeInterface $attribute,
        mixed $options
    ): bool {
        if ($options instanceof TagOptionsInterface
            && $attribute instanceof EavAttributeWithTagsInterface
            && $entity instanceof EavEntityWithTagsInterface
            && !$attribute->isForAllEavTags($options->getTagKey())
        ) {
            $tags = [];
            $tagKey = $options->getTagKey();
            foreach ($entity->getEavTags($tagKey) as $tag) {
                $tags[$tag->getId()] = 1;
            }

            foreach ($attribute->getEavTags($tagKey) as $attributeTag) {
                if (array_key_exists($attributeTag->getTag()->getId(), $tags)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @return EavOptionsInterface[]
     */
    protected function resolveOptions(
        EavEntityInterface $entity,
        EavAttributeInterface $attribute,
    ): array {
        $allAttributeOptions = $this->optionsRegistry->getByAttribute(get_class($attribute));
        $options = null;
        foreach ($allAttributeOptions as $oneOption) {
            if ($oneOption->getEntityFqcn() == get_class($entity)) {
                $options = $oneOption;
                break;
            }
        }

        if (!$options) {
            return [];
        }

        return $this->optionsRegistry->getByEav($options->getEavFqcn());
    }
}
