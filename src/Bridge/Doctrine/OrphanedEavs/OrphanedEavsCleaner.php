<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs;

use Doctrine\Persistence\ObjectManager;
use Maxkain\EavBundle\Bridge\Doctrine\Provider\DoctrineProvider;
use Maxkain\EavBundle\Options\EavOptionsRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class OrphanedEavsCleaner
{
    public function __construct(
        #[Autowire(service: OrphanedEavsQueryHandler::class)]
        protected OrphanedEavsHandlerInterface $handler,
        protected DoctrineProvider $doctrineProvider,
        protected ?EavOptionsRegistry $eavOptionsRegistry = null
    ) {
    }

    public function setHandler(OrphanedEavsHandlerInterface $handler): void
    {
        $this->handler = $handler;
    }

    public function clean(
        ?ObjectManager $em = null,
        ?string $eavFqcn = null,
        ?AffectedData $affectedData = null,
        ?int $batchSize = null,
        ?callable $qbModifier = null,
        ?EavOptionsRegistry $eavOptionsRegistry = null
    ): void {
        $eavOptionsRegistry ??= $this->eavOptionsRegistry;
        if (!$eavFqcn && !$eavOptionsRegistry) {
            throw new OrphanedEavsException('No EAV options specified.');
        }

        $eavFqcns = $eavFqcn ? [$eavFqcn] : $eavOptionsRegistry->getAllEavFqcns();
        foreach ($eavFqcns as $eavFqcn) {
            $em ??= $this->doctrineProvider->getEntityManager($eavFqcn);
            $this->handler->deleteOrphanedEavs($em, $eavFqcn, $affectedData, $batchSize, $qbModifier);
        }
    }
}
