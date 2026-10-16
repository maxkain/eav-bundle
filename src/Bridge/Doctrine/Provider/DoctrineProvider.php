<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

class DoctrineProvider
{
    public function __construct(
        protected ManagerRegistry $doctrine
    ) {
    }

    public function getEntityManager(string $fqcn): ObjectManager
    {
        return $this->doctrine->getManagerForClass($fqcn);
    }

    public function getRepository(string $fqcn): ObjectRepository
    {
        return $this->doctrine->getRepository($fqcn);
    }
}
