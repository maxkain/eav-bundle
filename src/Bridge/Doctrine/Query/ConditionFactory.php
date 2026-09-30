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

class ConditionFactory
{
    public function __construct(
        protected SubqueryContextFactory $subqueryContextFactory,
        protected ExpressionResolver $expressionResolver,
        protected EavTagQueryFactory $tagQueryFactory,
        protected EavOptionsRegistry $optionsRegistry
    ) {
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
        $options = $this->optionsRegistry->resolve($options);
        $expr = $qb->expr();

        $mainCondition = $expr->andX();
        $innerCondition = $orValueLogic ? $expr->orX() : $expr->andX();
        $this->addConditions($qb, $entityAlias, $attribute, $value, $innerCondition, $options);

        $data = $this->subqueryContextFactory->create($entityAlias, $attribute, $options, $value);
        $this->addInnerCondition($mainCondition, $innerCondition, $data);

        if ($tagConditionEnabled) {
            $mainCondition->add(
                $this->tagQueryFactory->createTagConditions($qb, $entityAlias, $attribute, $options)
            );
        }

        return $mainCondition;
    }

    protected function addConditions(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        mixed $value,
        Andx|Orx $innerCondition,
        EavOptionsInterface $options
    ): void {
        $values = is_array($value) ? $value : [$value];

        $i = 0;
        foreach ($values as $valueOne) {
            $data = $this->subqueryContextFactory->create($entityAlias, $attribute, $options, $valueOne, null, $i);
            $condition = $this->createCondition($qb, $data, $attribute, $valueOne);
            $this->addSubquery($innerCondition, $condition, $data);
            $i++;
        }
    }

    protected function createCondition(QueryBuilder $qb, EavSubqueryContext $data, mixed $attribute, mixed $valueOne): Andx|Orx
    {
        $expr = $qb->expr();

        return $expr->andX(
            $expr->eq($data->eavEntityPath, $data->entityIdPath),
            $expr->eq($data->eavAttributePath, $qb->createNamedParameter($attribute)),
            $this->expressionResolver->resolve($qb, $data->eavValuePath, $valueOne, $data->eavValueTitlePath)
        );
    }

    protected function addSubquery(Andx|Orx $innerCondition, Andx|Orx $condition, EavSubqueryContext $data): void
    {
        $subQb = $data->subQb;
        $subQb->select('1')->andWhere($condition);
        $innerCondition->add($subQb->expr()->exists($subQb));
    }

    protected function addInnerCondition(Andx $mainCondition, Andx|Orx $innerCondition, EavSubqueryContext $data): void
    {
        $mainCondition->add($innerCondition);
    }
}
