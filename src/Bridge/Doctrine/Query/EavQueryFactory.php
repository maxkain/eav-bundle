<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;
use Maxkain\EavBundle\Options\EavOptionsResolverInterface;
use Maxkain\EavBundle\Query\EavComparison;
use Maxkain\EavBundle\Query\EavExpression;

class EavQueryFactory
{
    public function __construct(
        protected ConditionFactory $conditionFactory,
        protected ValueSelectFactory $valueSelectFactory,
        protected ExpressionResolver $expressionResolver
    ) {
    }

    /**
     * @param array<scalar, array|scalar|EavValueInterface|EavExpression|EavComparison $attributeValues
     */
    public function addEavFilters(
        QueryBuilder $qb,
        string $entityAlias,
        EavOptionsInterface $options,
        array $attributeValues,
        bool $tagConditionEnabled = true,
        bool $orValueLogic = true,
        bool $orAttributeLogic = false
    ): static {
        $condition = $this->createEavFilters($qb, $entityAlias, $options, $attributeValues,
            $tagConditionEnabled, $orValueLogic, $orAttributeLogic);

        $qb->andWhere($condition);

        return $this;
    }

    /**
     * @param array<scalar, array|scalar|EavValueInterface|EavExpression|EavComparison $attributeValues
     */
    public function createEavFilters(
        QueryBuilder $qb,
        string $entityAlias,
        EavOptionsInterface $options,
        array $attributeValues,
        bool $tagConditionEnabled = true,
        bool $orValueLogic = true,
        bool $orAttributeLogic = false
    ): Andx|Orx {
        $expr = $qb->expr();
        $condition = $orAttributeLogic ? $expr->orX() : $expr->andX();
        foreach ($attributeValues as $attribute => $value) {
            $condition->add(
                $this->createEavCondition($qb, $entityAlias, $attribute, $options, $value, $tagConditionEnabled, $orValueLogic)
            );
        }

        return $condition;
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     * @param array|scalar|EavValueInterface|EavExpression|EavComparison $value
     */
    public function createEavCondition(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface $options,
        mixed $value,
        bool $tagConditionEnabled = true,
        bool $orValueLogic = true
    ): Andx {
        return $this->conditionFactory->createEavCondition($qb, $entityAlias, $attribute, $options, $value,
            $tagConditionEnabled, $orValueLogic);
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     * @param array|scalar|EavValueInterface|EavExpression|EavComparison|null $value
     */
    public function addValueSelect(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface $options,
        mixed $value = null,
        bool $tagConditionEnabled = true,
        string $select = 'MIN(:value)',
        string $as = 'HIDDEN :attributeName'
    ): static {
        $this->valueSelectFactory->addValueSelect($qb, $entityAlias, $attribute, $options, $value,
            $tagConditionEnabled, $select, $as);

        return $this;
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     * @param array|scalar|EavValueInterface|EavExpression|EavComparison|null $value
     */
    public function createValueSelect(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface $options,
        mixed $value = null,
        bool $tagConditionEnabled = true,
        string $select = 'MIN(:value)'
    ): QueryBuilder {
        return $this->valueSelectFactory->createQb($qb, $entityAlias, $attribute, $options, $value,
            $tagConditionEnabled, $select);
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     */
    public function getSelectAttributeName(EavOptionsInterface $options, mixed $attribute): string
    {
        return $this->valueSelectFactory->getAttributeName($options, $attribute);
    }

    public function getQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        return $qb;
    }
}
