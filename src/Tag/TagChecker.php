<?php

namespace Maxkain\EavBundle\Tag;

use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavEntityInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeWithTagsInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavEntityWithTagsInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavTagInterface;
use Maxkain\EavBundle\Tag\Options\EavOptionsWithTagsInterface;
use Maxkain\EavBundle\Tag\Options\TagOptionsInterface;

class TagChecker
{
    public function check(
        EavEntityInterface $entity,
        EavAttributeInterface $attribute,
        object $options,
    ): bool {
        if (!$options instanceof EavOptionsWithTagsInterface || !$options->getTagOptions()) {
            return true;
        }

        $isOrTagLogic = $options->isOrTagLogic();
        foreach ($options->getTagOptions() as $tagOptions) {
            if ($this->checkOne($entity, $attribute, $tagOptions)) {
                if ($isOrTagLogic) {
                    return true;
                }
            } else {
                if (!$isOrTagLogic) {
                    return false;
                }
            }
        }

        return !$isOrTagLogic;
    }

    protected function checkOne(
        EavEntityInterface $entity,
        EavAttributeInterface $attribute,
        TagOptionsInterface $options
    ): bool {
        if ($attribute instanceof EavAttributeWithTagsInterface
            && $entity instanceof EavEntityWithTagsInterface
            && !$attribute->isForAllEavTags($options->getTagFqcn())
        ) {
            $tags = [];
            $tagKey = $options->getTagFqcn();
            $eavTags = $entity->getEavTags($tagKey);
            $eavTags = $this->resolveEntityEavTags($eavTags);

            foreach ($eavTags as $tag) {
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
     * @param EavTagInterface|iterable<EavTagInterface>|null $tags
     * @return iterable<EavTagInterface>
     */
    protected function resolveEntityEavTags(mixed $tags): iterable
    {
        if (!is_iterable($tags)) {
            if ($tags) {
                return [$tags];
            }

            return [];
        }

        return $tags;
    }
}
