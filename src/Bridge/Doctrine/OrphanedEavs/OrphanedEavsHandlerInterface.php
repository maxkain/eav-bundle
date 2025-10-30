<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Doctrine\Persistence\ObjectManager;

interface OrphanedEavsHandlerInterface
{
    public function deleteOrphanedEavs(ObjectManager $em, string $eavFqcn, AffectedData $affectedData): void;
}
