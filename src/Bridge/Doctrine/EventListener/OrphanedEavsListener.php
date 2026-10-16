<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs\AffectedDataExtractor;
use Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs\OrphanedEavsQueryHandler;
use Maxkain\EavBundle\Bridge\Doctrine\OrphanedEavs\OrphanedEavsHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class OrphanedEavsListener
{
    protected bool $enabled = true;
    protected array $data;

    public function __construct(
        #[Autowire(service: OrphanedEavsQueryHandler::class)]
        protected OrphanedEavsHandlerInterface $handler,
        protected AffectedDataExtractor $extractor,
    ) {
    }

    public function setHandler(OrphanedEavsHandlerInterface $handler): void
    {
        $this->handler = $handler;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->data = $this->extractor->extract($args->getObjectManager());
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (!$this->enabled) {
            return;
        }

        $eventManager = $args->getObjectManager()->getEventManager();
        $eventManager->removeEventListener(Events::onFlush, $this);
        $eventManager->removeEventListener(Events::postFlush, $this);

        try {
            foreach ($this->data as $eavFqcn => $affectedData) {
                $this->handler->deleteOrphanedEavs($args->getObjectManager(), $eavFqcn, $affectedData);
            }
        } finally {
            $eventManager->addEventListener(Events::onFlush, $this);
            $eventManager->addEventListener(Events::postFlush, $this);
        }
    }
}
