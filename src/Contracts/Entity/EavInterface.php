<?php

namespace Maxkain\EavBundle\Contracts\Entity;

use Maxkain\EavBundle\Utils\CollectionSetter\CollectionItemIdentityInterface;

interface EavInterface extends CollectionItemIdentityInterface
{
    public function getId(): mixed;
    public function getEntity() : EavEntityInterface;
    public function setEntity(EavEntityInterface $entity): static;
    public function getAttribute() : EavAttributeInterface;
    public function setAttribute(EavAttributeInterface $attribute): static;
    public function getValue(): mixed;
    public function setValue(mixed $value): static;
}
