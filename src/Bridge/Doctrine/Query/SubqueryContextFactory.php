<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Maxkain\EavBundle\Options\EavOptionsRegistry;
use Maxkain\EavBundle\Query\EavComparison;
use Maxkain\EavBundle\Query\EavExpression;

class SubqueryContextFactory
{
    public function __construct(
        protected EavOptionsRegistry $optionsRegistry,
        protected AliasGenerator $aliasGenerator,
        protected EntityManagerInterface $em
    ) {
    }

    /**
     * @param scalar|EavAttributeInterface $attribute
     * @param array|scalar|EavValueInterface|EavExpression|EavComparison|null $value
     */
    public function create(
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface|string $options,
        mixed $value = null,
        ?string $aliasPrefix = null,
        ?int $iteration = null
    ): EavSubqueryContext {
        $em = $this->em;
        $data = new EavSubqueryContext();
        $data->iteration = $iteration;
        $data->options = $this->optionsRegistry->resolve($options);
        $options = $data->options;
        $mapping = $options->getPropertyMapping();
        $data->entityAlias = $entityAlias;
        $data->entityIdPath = $entityAlias . '.' . $mapping->getEntityId();
        $data->attribute = $attribute;
        $data->values = [];
        if ($value !== null) {
            $data->values = is_array($value) ? $value : [$value];
        }
        $data->eavAlias = $this->aliasGenerator->generate($aliasPrefix . 'eav', $options->getIndex(), $attribute, $iteration);
        $data->eavValueAlias = $this->aliasGenerator->generate($aliasPrefix . 'eav_value', $options->getIndex(), $attribute, $iteration);
        $data->eavEntityPath = $data->eavAlias . '.' . $mapping->getEntity();
        $data->eavAttributePath = $data->eavAlias . '.' . $mapping->getAttribute();
        $data->eavValuePath = $data->eavAlias . '.' . $mapping->getValue();
        $data->eavValueTitlePath = null;
        $data->subQb = $em->getRepository($options->getEavFqcn())->createQueryBuilder($data->eavAlias);
        if ($options->getValueFqcn()) {
            $data->subQb->leftJoin($data->eavValuePath, $data->eavValueAlias);
            $data->eavValueTitlePath = $data->eavValueAlias . '.' . $mapping->getValueTitle();
        }

        return $data;
    }
}
