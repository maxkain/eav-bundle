<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeWithTagsInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;

class EavTagQueryFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected EavOptionsRegistry $optionsRegistry,
        protected AliasGenerator $aliasGenerator
    ) {
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     */
    public function createTagConditions(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface $options,
    ): Orx {
        $tagConditions = new Orx();
        $options = $this->optionsRegistry->getByEav($options->getEavFqcn());
        $i = 0;

        foreach ($options as $option) {
            $tagCondition = $this->createTagCondition($qb, $entityAlias, $attribute, $option, $i);
            if ($tagCondition) {
                $tagConditions->add($tagCondition);
                $i++;
            }
        }

        return $tagConditions;
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     */
    public function createTagCondition(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface $options,
        int $index = 0
    ): ?Func {
        $em = $this->em;
        $mapping = $options->getPropertyMapping();
        $entityIdPath = $entityAlias . '.' . $mapping->getEntityId();
        $attribute = is_scalar($attribute)
            ? $em->getRepository($options->getAttributeFqcn())->find($attribute) : $attribute;

        $tagCondition = null;
        if ($attribute instanceof EavAttributeWithTagsInterface
            && !$attribute->isForAllEavTags($options->getTagKey())
        ) {
            $attributeTagQb = $this->createAttributeTagQb($attribute, $qb, $options, $index);
            $expr = $attributeTagQb->expr();

            if ($options->isMultipleTags()) {
                $innerEntityQb = $this->createInnerEntityQb($entityIdPath, $attribute, $attributeTagQb, $options, $index);
                $tagCondition = $expr->exists($innerEntityQb->getDQL());
            } else {
                $tagPath = $entityAlias . '.' . $mapping->getEntityTag();
                $tagCondition = $expr->in($tagPath, $attributeTagQb->getDQL());
            }
        }

        return $tagCondition;
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     */
    public function createAttributeTagQb(
        mixed $attribute,
        QueryBuilder $mainQb,
        EavOptionsInterface $options,
        int $index = 0
    ): QueryBuilder {
        $mapping = $options->getPropertyMapping();
        $attributeTagFqcn = $options->getAttributeTagFqcn();
        $attributeTagAlias = $this->aliasGenerator->generate('attributeTag', $options->getIndex(), $attribute, $index);
        $attributeTagTagPath = $attributeTagAlias . '.' . $mapping->getAttributeTagTag();
        $attributeTagAttributePath = $attributeTagAlias . '.' . $mapping->getAttributeTagAttribute();

        $attributeTagQb = $this->em->getRepository($attributeTagFqcn)
            ->createQueryBuilder($attributeTagAlias);

        $expr = $attributeTagQb->expr();
        $attributeTagQb->select('IDENTITY(' . $attributeTagTagPath . ')')
            ->where(
                $expr->eq(
                    $mainQb->createNamedParameter($attribute),
                    $attributeTagAttributePath
                )
            );

        return $attributeTagQb;
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     */
    public function createInnerEntityQb(
        string $entityIdPath,
        mixed $attribute,
        QueryBuilder $attributeTagQb,
        EavOptionsInterface $options,
        int $index = 0
    ): QueryBuilder {
        $mapping = $options->getPropertyMapping();
        $innerEntityAlias = $this->aliasGenerator->generate('innerEntity', $options->getIndex(), $attribute, $index);
        $innerEntityIdPath = $innerEntityAlias . '.' . $mapping->getEntityId();
        $innerEntityTagAlias = $this->aliasGenerator->generate('innerTag', $options->getIndex(), $attribute, $index);
        $innerEntityTagPath = $innerEntityAlias . '.' . $mapping->getEntityTags();

        $innerEntityQb = $this->em->getRepository($options->getEntityFqcn())
            ->createQueryBuilder($innerEntityAlias);

        $expr = $innerEntityQb->expr();
        $innerEntityQb->select('1')
            ->join($innerEntityTagPath, $innerEntityTagAlias)
            ->where(
                $expr->eq($entityIdPath, $innerEntityIdPath)
            )
            ->andWhere(
                $expr->in($innerEntityTagAlias, $attributeTagQb->getDQL())
            )
        ;

        return $innerEntityQb;
    }
}
