<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Doctrine\ORM\QueryBuilder;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Maxkain\EavBundle\Query\EavComparison;
use Maxkain\EavBundle\Query\EavExpression;

class EavSubqueryContext
{
    public ?int $iteration;
    public EavOptionsInterface $options;
    public string $entityAlias;
    public string $entityIdPath;

    /**
     * @var scalar|EavAttributeInterface
     */
    public mixed $attribute;

    /**
     * @var array<scalar|EavValueInterface|EavExpression|EavComparison>
     */
    public array $values;
    public string $eavAlias;
    public string $eavValueAlias;
    public string $eavEntityPath;
    public string $eavAttributePath;
    public string $eavValuePath;
    public ?string $eavValueTitlePath;
    public QueryBuilder $subQb;
}
