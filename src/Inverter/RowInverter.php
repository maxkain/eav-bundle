<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\AttributeChecker\AttributeChecker;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavEntityInterface;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Contracts\Entity\EavEnumAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Contracts\Repository\AttributeRepositoryInterface;
use Maxkain\EavBundle\Contracts\Repository\EntityRepositoryInterface;
use Maxkain\EavBundle\Contracts\Repository\EavRepositoryInterface;
use Maxkain\EavBundle\Contracts\Repository\ValueRepositoryInterface;
use Maxkain\EavBundle\Inverter\Options\InverterOptionsInterface;

class RowInverter
{
    protected InverterOptionsInterface $options;

    public function __construct(
        protected InverterViolationFactory $inverterViolationFactory,
        protected AttributeChecker $attributeChecker,
        protected EavRepositoryInterface $eavRepository,
        protected EntityRepositoryInterface $entityRepository,
        protected AttributeRepositoryInterface $attributeRepository,
        protected ValueRepositoryInterface $valueRepository
    ) {
    }

    /**
     * @param scalar $value Plain value or valueId.
     */
    public function invert(
        mixed $entity,
        mixed $attribute,
        mixed $value,
        array $keyCriteria,
        int $itemIndex,
        ?int $valueIndex,
        InverterOptionsInterface $options,
    ): EavInterface|EavInverterViolation|null {
        if ($options->isIgnoreInputEmptyValue() && empty($value)) {
            return null;
        }

        $this->options = $options;
        $mapping = $options->getPropertyMapping();
        $reverseMapping = $options->getReversePropertyMapping();
        $attributeName = $reverseMapping->getAttribute();
        $valueName = $options->isMultiple() ? $reverseMapping->getValues() : $reverseMapping->getValue();
        $attributePath = [$itemIndex, $attributeName];
        $valuePath = [$itemIndex, $valueName];
        if (isset($valueIndex)) {
            $valuePath[] = $valueIndex;
        }

        $eav = $this->eavRepository->findOneBy($options->getEavFqcn(), $keyCriteria) ?? $this->createEav();

        $resultEntity = is_object($entity) ? $entity
            : $this->entityRepository->findOneBy($options->getEntityFqcn(), [$mapping->getEntityId() => $entity]);

        if (!$resultEntity) {
            return $this->createViolation('Entity not found', [], []);
        }

        $resultAttribute = is_object($attribute) ? $attribute
            : $this->attributeRepository->findOneBy($options->getAttributeFqcn(), [$mapping->getAttributeId() => $attribute]);
        if (!$resultAttribute || !$this->checkAttribute($resultEntity, $resultAttribute, $options)) {
            return $this->createViolation('Attribute not found', [], $attributePath);
        }

        $resultValue = $value;
        if ($options->getValueFqcn()) {
            $resultValue = is_object($value) ? $value
                : $this->valueRepository->findOneBy($options->getValueFqcn(), [$mapping->getValueId() => $value]);
            if (!$resultValue || !$this->checkAttributeValue($resultAttribute, $resultValue)) {
                return $this->createViolation('Value not found', [], $valuePath);
            }
        }

        $eav->setEntity($resultEntity);
        $eav->setAttribute($resultAttribute);
        $eav->setValue($resultValue);

        return $eav;
    }

    protected function createViolation(string $message, array $parameters, array $path): EavInverterViolation
    {
        return $this->inverterViolationFactory->create($message, $parameters, $path);
    }

    protected function checkAttribute(EavEntityInterface $entity, EavAttributeInterface $attribute, object $options): bool
    {
        return $this->attributeChecker->check($entity, $attribute, $options);
    }

    protected function checkAttributeValue(EavEnumAttributeInterface $attribute, EavValueInterface $value): bool
    {
        foreach ($attribute->getValues() as $attributeValue) {
            if ($value->getId() == $attributeValue->getId()) {
                return true;
            }
        }

        return false;
    }

    protected function createEav(): EavInterface
    {
        return new ($this->options->getEavFqcn());
    }
}
