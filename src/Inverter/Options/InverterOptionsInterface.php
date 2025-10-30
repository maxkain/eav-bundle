<?php

namespace Maxkain\EavBundle\Inverter\Options;

interface InverterOptionsInterface
{
    public function getEavFqcn(): string;
    public function getEntityFqcn(): string;
    public function getAttributeFqcn(): string;
    public function getValueFqcn(): ?string;
    public function isMultiple(): bool;

    /**
     * @return ?string One of gettype() values or null for any type.
     */
    public function getEntityInputType(): ?string;

    /**
     * @return ?string One of gettype() values or null for any type.
     */
    public function getAttributeInputType(): ?string;

    /**
     * @return ?string One of gettype() values or null for any type.
     */
    public function getValueInputType(): ?string;
    public function isIgnoreInputEmptyValue(): bool;
    public function getPropertyMapping(): InverterPropertyMappingInterface;
    public function getReversePropertyMapping(): InverterReversePropertyMappingInterface;
}
