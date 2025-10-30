<?php

namespace Maxkain\EavBundle\Converter\Options;

interface ConverterReversePropertyMappingInterface
{
    public function getAttribute(): string;
    public function getAttributeName(): string;
    public function getValues(): string;
    public function getValue(): string;
    public function getValueTitles(): string;
    public function getValueTitle(): string;
}
