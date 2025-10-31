<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;
use Maxkain\EavBundle\Query\EavComparison;
use Maxkain\EavBundle\Query\EavExpression;

class EavQueryFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected EavOptionsRegistry $optionsRegistry,
        protected EavTagQueryFactory $tagQueryFactory,
        protected AliasGenerator $aliasGenerator
    ) {
    }

    /**
     * @param array<scalar, scalar|object|array<scalar|object> $attributeValues
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
     * @param scalar|EavValueInterface|EavExpression|EavComparison $value
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
     * @param array<scalar, scalar|object|array<scalar|object> $attributeValues
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
     * @param scalar|EavValueInterface|EavExpression|EavComparison $value
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
        if (!$options) {
            throw new \InvalidArgumentException('Options not found.');
        }

        $em = $this->em;
        $mapping = $options->getPropertyMapping();
        $entityIdPath = $entityAlias . '.' . $mapping->getEntityId();

        $values = is_array($value) ? $value : [$value];
        $expr = $qb->expr();
        $mainCondition = $expr->andX();

        $i = 0;
        foreach ($values as $value) {
            $eavAlias = $this->aliasGenerator->generate('eav', $options->getIndex(), $attribute, $i);
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

        if ($tagConditionEnabled) {
            $mainCondition->add(
                $this->tagQueryFactory->createTagConditions($qb, $entityAlias, $attribute, $options)
            );
        }

        return $mainCondition;
    }

    public function resolveExpression(QueryBuilder $qb, string $eavValuePath, mixed $value): Expr\Comparison|string
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
}
