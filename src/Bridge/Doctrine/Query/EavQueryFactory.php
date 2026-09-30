<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
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
        protected EavOptionsRegistry $optionsRegistry,
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
        EavOptionsInterface|string $options,
        array $attributeValues,
        bool $tagConditionEnabled = true,
        bool $orValueLogic = false,
        bool $orAttributeLogic = false,
        bool $orEavLogic = false
    ): QueryBuilder {
        $condition = $this->createEavFilters($qb, $entityAlias, $options, $attributeValues,
            $tagConditionEnabled, $orValueLogic, $orAttributeLogic);

        return $orEavLogic ? $qb->orWhere($condition) : $qb->andWhere($condition);
    }

    /**
     * @param array<scalar, array|scalar|EavValueInterface|EavExpression|EavComparison $attributeValues
     */
    public function createEavFilters(
        QueryBuilder $qb,
        string $entityAlias,
        EavOptionsInterface|string $options,
        array $attributeValues,
        bool $tagConditionEnabled = true,
        bool $orValueLogic = false,
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
        EavOptionsInterface|string $options,
        mixed $value,
        bool $tagConditionEnabled = true,
        bool $orValueLogic = false
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
        EavOptionsInterface|string $options,
        mixed $value = null,
        bool $tagConditionEnabled = true,
        string $select = 'MIN(:value)',
        string $as = 'HIDDEN :attributeName'
    ): QueryBuilder {
        return $this->valueSelectFactory->addValueSelect($qb, $entityAlias, $attribute, $options, $value, $tagConditionEnabled, $select, $as);
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     * @param array|scalar|EavValueInterface|EavExpression|EavComparison|null $value
     */
    public function createValueSelect(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface|string $options,
        mixed $value = null,
        bool $tagConditionEnabled = true,
        string $select = 'MIN(:value)'
    ): QueryBuilder {
        return $this->valueSelectFactory->create($qb, $entityAlias, $attribute, $options, $value, $tagConditionEnabled, $select);
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     */
    public function getAttributeName(EavOptionsInterface|string $options, mixed $attribute): string
    {
        return $this->valueSelectFactory->getAttributeName($options, $attribute);
    }
}
