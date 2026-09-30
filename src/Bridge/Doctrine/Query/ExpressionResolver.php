<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Doctrine\ORM\QueryBuilder;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Query\EavComparison;
use Maxkain\EavBundle\Query\EavExpression;
use Doctrine\ORM\Query\Expr\Comparison;

class ExpressionResolver
{
    public function __construct(
        protected PlaceholderReplacer $placeholderReplacer
    ) {
    }

    /**
     * @param scalar|EavValueInterface|EavExpression|EavComparison $valueOne
     */
    public function resolve(
        QueryBuilder $qb,
        string $eavValuePath,
        mixed $valueOne,
        ?string $eavValueTitlePath = null
    ): Comparison|string {
        if ($valueOne instanceof EavExpression) {
            $search = ['field'];
            $replace = [$eavValuePath];
            if ($eavValueTitlePath) {
                $search[] = 'fieldTitle';
                $replace[] = $eavValueTitlePath;
            }

            return $this->replacePlaceholder($search, $replace, $valueOne->getExpression());
        }

        $operator = '=';
        $valuePath = $eavValuePath;
        if ($valueOne instanceof EavComparison) {
            $operator = $valueOne->getOperator();
            $valueOne = $valueOne->getValue();
        }

        if ($eavValueTitlePath && $operator != '=') {
            $valuePath = $eavValueTitlePath;
        }

        return new Comparison($valuePath, $operator, $qb->createNamedParameter($valueOne));
    }

    protected function replacePlaceholder(array|string $search, array|string $replace, string $subject): string
    {
        return $this->placeholderReplacer->replace($search, $replace, $subject);
    }
}
