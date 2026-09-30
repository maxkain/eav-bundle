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

class ValueSelectFactory
{
    public function __construct(
        protected SubqueryContextFactory $subqueryContextFactory,
        protected ExpressionResolver $expressionResolver,
        protected EavTagQueryFactory $tagQueryFactory,
        protected EavOptionsRegistry $optionsRegistry,
        protected AliasGenerator $aliasGenerator,
        protected PlaceholderReplacer $placeholderReplacer
    ) {
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
        $options = $this->optionsRegistry->resolve($options);

        $selectQb = $this->create($qb, $entityAlias, $attribute, $options, $value, $tagConditionEnabled, $select);
        $attributeName = $this->getAttributeName($options, $attribute);
        $as = $this->replacePlaceholder('attributeName', $attributeName, $as);
        $qb->addSelect('(' . $selectQb->getDQL() . ') AS ' . $as);

        return $qb;
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     * @param array|scalar|EavValueInterface|EavExpression|EavComparison|null $value
     */
    public function create(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface|string $options,
        mixed $value = null,
        bool $tagConditionEnabled = true,
        string $select = 'MIN(:value)'
    ): QueryBuilder {
        $aliasPrefix = 'select_';
        $data = $this->subqueryContextFactory->create($entityAlias, $attribute, $options, $value, $aliasPrefix);
        $options = $data->options;

        $mainCondition = $this->createMainCondition($qb, $attribute, $data);
        $innerCondition = $this->createInnerCondition($qb, $value, $data);

        $mainCondition->add($innerCondition);
        if ($tagConditionEnabled) {
            $mainCondition->add(
                $this->tagQueryFactory->createTagConditions($qb, $entityAlias, $attribute, $options, $aliasPrefix)
            );
        }

        $replace = $data->eavValueTitlePath ?? $data->eavValuePath;
        $selectClause = $this->replacePlaceholder('value', $replace, $select);
        $data->subQb->select($selectClause)->where($mainCondition);

        return $data->subQb;
    }

    protected function createMainCondition(QueryBuilder $qb, mixed $attribute, EavSubqueryContext $data): Andx
    {
        $expr = $qb->expr();
        return $expr->andX(
            $expr->eq($data->eavEntityPath, $data->entityIdPath),
            $expr->eq($data->eavAttributePath, $qb->createNamedParameter($attribute)),
        );
    }

    protected function createInnerCondition(QueryBuilder $qb, mixed $value, EavSubqueryContext $data): Orx
    {
        $innerCondition = $qb->expr()->orX();
        if ($value !== null) {
            foreach ($data->values as $value) {
                $innerCondition->add(
                    $this->expressionResolver->resolve($qb, $data->eavValuePath, $value, $data->eavValueTitlePath)
                );
            }
        }

        return $innerCondition;
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     */
    public function getAttributeName(EavOptionsInterface|string $options, mixed $attribute): string
    {
        $options = $this->optionsRegistry->resolve($options);
        return $this->aliasGenerator->generate('attribute', $options->getIndex(), $attribute);
    }

    protected function replacePlaceholder(array|string $search, array|string $replace, string $subject): string
    {
        return $this->placeholderReplacer->replace($search, $replace, $subject);
    }
}
