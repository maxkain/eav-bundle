<?php

namespace Maxkain\EavBundle\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Maxkain\EavBundle\Attribute\AttributeChecker;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeWithTagsInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;
use Maxkain\EavBundle\Query\EavComparison;
use Maxkain\EavBundle\Query\EavExpression;

class EavQueryFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected EavOptionsRegistry $optionsRegistry,
        protected AttributeChecker $attributeResolver
    ) {
    }

    /**
     * @param array<scalar, scalar|EavValueInterface|array<scalar|EavValueInterface> $attributeValues
     */
    public function addEavFilters(
        QueryBuilder $qb,
        string $entityAlias,
        EavOptionsInterface|string $options,
        array $attributeValues,
        bool $tagConditionEnabled = true
    ): QueryBuilder {
        return $qb->andWhere($this->createEavFilters($qb, $entityAlias, $options, $attributeValues,
            $tagConditionEnabled));
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     * @param scalar|EavValueInterface|array<scalar|EavValueInterface> $value
     */
    public function addEavFilter(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface|string $options,
        mixed $value,
        bool $tagConditionEnabled = true
    ): QueryBuilder {
        return $qb->andWhere($this->createEavCondition($qb, $entityAlias, $attribute, $options, $value,
            $tagConditionEnabled));
    }

    /**
     * @param array<scalar, scalar|EavValueInterface|array<scalar|EavValueInterface> $attributeValues
     */
    public function createEavFilters(
        QueryBuilder $qb,
        string $entityAlias,
        EavOptionsInterface|string $options,
        array $attributeValues,
        bool $tagConditionEnabled = true,
    ): Andx {
        $andX = $qb->expr()->andX();
        foreach ($attributeValues as $attribute => $value) {
            $andX->add(
                $this->createEavCondition($qb, $entityAlias, $attribute, $options, $value, $tagConditionEnabled)
            );
        }

        return $andX;
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     * @param scalar|EavValueInterface|array<scalar|EavValueInterface> $value
     */
    public function createEavCondition(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface|string $options,
        mixed $value,
        bool $tagConditionEnabled = true
    ): Andx {
        $options = $this->optionsRegistry->resolve($options);
        $em = $this->em;
        $mapping = $options->getPropertyMapping();
        $entityIdPath = $entityAlias . '.' . $mapping->getEntityId();

        $values = is_array($value) ? $value : [$value];
        $expr = $qb->expr();
        $mainCondition = $expr->andX();

        $i = 0;
        foreach ($values as $value) {
            $eavAlias = $this->generateAlias('eav', $options->getIndex(), $attribute, $i);
            $eavEntityPath = $eavAlias . '.' . $mapping->getEntity();
            $eavAttributePath = $eavAlias . '.' . $mapping->getAttribute();
            $eavValuePath = $eavAlias . '.' . $mapping->getValue();

            $subQb = $em->getRepository($options->getEavFqcn())->createQueryBuilder($eavAlias);
            $condition = $expr->andX(
                $expr->eq($eavEntityPath, $entityIdPath),
                $expr->eq($eavAttributePath, $qb->createNamedParameter($attribute)),
                $this->resolveExpression($qb, $eavValuePath, $value)
            );

            $mainCondition->add(
                $expr->exists(
                    $subQb->select('1')->where($condition)
                )
            );

            $i++;
        }

        $tagConditions = new Orx();
        if ($tagConditionEnabled) {
            $options = $this->optionsRegistry->getByEav($options->getEavFqcn());
            $i = 0;
            foreach ($options as $option) {
                $tagCondition = $this->createTagCondition($qb, $entityAlias, $attribute, $option, $i);
                if ($tagCondition) {
                    $tagConditions->add($tagCondition);
                    $i++;
                }
            }
        }

        if ($tagConditions->count()) {
            $mainCondition->add($tagConditions);
        }

        return $mainCondition;
    }

    protected function resolveExpression(QueryBuilder $qb, string $eavValuePath, mixed $value): Expr\Comparison|string
    {
        if ($value instanceof EavExpression) {
            return str_replace(':field', $eavValuePath, $value->getExpression());
        }

        $operator = '=';
        if ($value instanceof EavComparison) {
            $operator = $value->getOperator();
            $value = $value->getValue();
        }

        return new Expr\Comparison($eavValuePath, $operator, $qb->createNamedParameter($value));
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
        $attributeTagAlias = $this->generateAlias('attributeTag', $options->getIndex(), $attribute, $index);
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
        $innerEntityAlias = $this->generateAlias('innerEntity', $options->getIndex(), $attribute, $index);
        $innerEntityIdPath = $innerEntityAlias . '.' . $mapping->getEntityId();
        $innerEntityTagAlias = $this->generateAlias('innerTag', $options->getIndex(), $attribute, $index);
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

    public function generateAlias(string $name, int $eavIndex, mixed $attribute, mixed $suffix = null): string
    {
        $attributeId = $attribute instanceof EavAttributeInterface ? $attribute->getId() : $attribute;
        $alias = $name . '_' . $eavIndex . '_' . $attributeId;
        if (isset($suffix)) {
            $alias .= '_' . $suffix;
        }

        return $alias;
    }
}
