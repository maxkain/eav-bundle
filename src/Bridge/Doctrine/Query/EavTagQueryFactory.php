<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use Maxkain\EavBundle\Bridge\Doctrine\Provider\DoctrineProvider;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeWithTagsInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Maxkain\EavBundle\Tag\Options\TagOptionsInterface;

class EavTagQueryFactory
{
    public function __construct(
        protected DoctrineProvider $doctrineProvider,
        protected AliasGenerator $aliasGenerator
    ) {
    }

    /**
     * @param scalar|EavAttributeInterface|QueryFieldPath $attribute
     */
    public function createTagConditions(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        EavOptionsInterface $options,
        string $aliasPrefix = ''
    ): Andx|Orx {
        $tagConditions = $options->isOrTagLogic() ? new Orx() : new Andx();

        $i = 0;
        foreach ($options->getTagOptions() as $tagOptions) {
            $tagCondition = $this->createTagCondition($qb, $entityAlias, $attribute, $tagOptions, $options, $i,
                $aliasPrefix);
            if ($tagCondition->count()) {
                $tagConditions->add($tagCondition);
                $i++;
            }
        }

        return $tagConditions;
    }

    /**
     * @param scalar|EavAttributeInterface|QueryFieldPath $attribute
     */
    protected function createTagCondition(
        QueryBuilder $qb,
        string $entityAlias,
        mixed $attribute,
        TagOptionsInterface $tagOptions,
        EavOptionsInterface $options,
        int $index = 0,
        string $aliasPrefix = ''
    ): Orx {
        $mapping = $tagOptions->getPropertyMapping();
        $attribute = is_scalar($attribute)
            ? $this->getRepository($options->getAttributeFqcn())->find($attribute) : $attribute;

        if ($attribute === null) {
            throw new EavQueryException('Attribute is not valid.');
        }

        $tagCondition = new Orx();
        if ($attribute instanceof QueryFieldPath ||
            $attribute instanceof EavAttributeWithTagsInterface
            && !$attribute->isForAllEavTags($tagOptions->getTagFqcn())
        ) {
            $attributeTagQb = $this->createAttributeTagQb($qb,$attribute, $tagOptions, $options, $index, $aliasPrefix);
            $expr = $attributeTagQb->expr();

            if ($attribute instanceof QueryFieldPath) {
                $forAllQb = $this->createForAllQb($qb, $attribute, $tagOptions, $options, $index, $aliasPrefix);
                $forAllCondition = $expr->exists($forAllQb);
                $tagCondition->add($forAllCondition);
            }

            if ($tagOptions->isMultipleTags()) {
                $innerEntityQb = $this->createInnerTagQb($entityAlias, $attribute, $attributeTagQb, $tagOptions,
                    $options, $index, $aliasPrefix);
                $condition = $expr->exists($innerEntityQb->getDQL());
            } else {
                $tagPath = $entityAlias . '.' . $mapping->getEntityTag();
                $condition = $expr->in($tagPath, $attributeTagQb->getDQL());
                $condition = new Andx(["$tagPath IS NOT NULL", $condition]);
            }

            $tagCondition->add($condition);
        }

        return $tagCondition;
    }

    protected function createForAllQb(
        QueryBuilder $qb,
        QueryFieldPath $attributePath,
        TagOptionsInterface $tagOptions,
        EavOptionsInterface $options,
        int $index = 0,
        string $aliasPrefix = ''
    ): QueryBuilder {
        $expr = $qb->expr();
        $attributeName = $aliasPrefix . 'attributeForAllTags';
        $attributeAlias = $this->aliasGenerator->generate($attributeName, $options->getIndex(), $attributePath, $index);
        $tagMapping = $tagOptions->getPropertyMapping();
        $attributeForAllTagsPath = $attributeAlias . '.' . $tagMapping->getAttributeForAllTags();
        $forAllQb = $this->getRepository($options->getAttributeFqcn())->createQueryBuilder($attributeAlias);

        return $forAllQb->select('1')
            ->where($expr->eq($attributeAlias, (string) $attributePath))
            ->andWhere(
                $expr->eq($attributeForAllTagsPath, $qb->createNamedParameter(true))
            );
    }

    protected function createAttributeTagQb(
        QueryBuilder $qb,
        EavAttributeInterface|QueryFieldPath $attribute,
        TagOptionsInterface $tagOptions,
        EavOptionsInterface $options,
        int $index = 0,
        string $aliasPrefix = ''
    ): QueryBuilder {
        $tagMapping = $tagOptions->getPropertyMapping();
        $attributeTagFqcn = $tagOptions->getAttributeTagFqcn();
        $attributeTagName = $aliasPrefix . 'attributeTag';
        $attributeTagAlias = $this->aliasGenerator->generate($attributeTagName, $options->getIndex(), $attribute, $index);
        $attributeTagTagPath = $attributeTagAlias . '.' . $tagMapping->getAttributeTagTag();
        $attributeTagAttributePath = $attributeTagAlias . '.' . $tagMapping->getAttributeTagAttribute();

        $attributeTagQb = $this->getRepository($attributeTagFqcn)->createQueryBuilder($attributeTagAlias);
        $expr = $attributeTagQb->expr();
        $attributeParameter = $attribute instanceof EavAttributeInterface
                ? $qb->createNamedParameter($attribute)
                : (string) $attribute;
        $attributeTagQb->select('IDENTITY(' . $attributeTagTagPath . ')')
            ->where(
                $expr->eq(
                    $attributeTagAttributePath,
                    $attributeParameter
                )
            );

        return $attributeTagQb;
    }

    protected function createInnerTagQb(
        string $entityAlias,
        EavAttributeInterface|QueryFieldPath $attribute,
        QueryBuilder $attributeTagQb,
        TagOptionsInterface $tagOptions,
        EavOptionsInterface $options,
        int $index = 0,
        string $aliasPrefix = ''
    ): QueryBuilder {
        $tagMapping = $tagOptions->getPropertyMapping();
        $innerTagName = $aliasPrefix . 'innerTag';
        $innerTagAlias = $this->aliasGenerator->generate($innerTagName, $options->getIndex(), $attribute, $index);
        $entityTagPath = $entityAlias . '.' . $tagMapping->getEntityTags();

        $innerTagQb = $this->getRepository($tagOptions->getTagFqcn())->createQueryBuilder($innerTagAlias);
        $expr = $innerTagQb->expr();
        $innerTagQb->select('1')
            ->where(
                "$innerTagAlias MEMBER OF $entityTagPath",
            )
            ->andWhere(
                $expr->in($innerTagAlias, $attributeTagQb->getDQL())
            )
        ;

        return $innerTagQb;
    }

    protected function getRepository(string $fqcn): ObjectRepository
    {
        return $this->doctrineProvider->getRepository($fqcn);
    }
}
