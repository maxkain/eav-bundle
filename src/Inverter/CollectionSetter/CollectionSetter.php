<?php

namespace Maxkain\EavBundle\Inverter\CollectionSetter;

class CollectionSetter
{
    /**
     * @param iterable<CollectionItemIdentityInterface> $from
     * @param iterable<CollectionItemIdentityInterface> $to
     * @return iterable<CollectionItemIdentityInterface>
     */
    public function add(iterable $from, iterable $to, ?callable $orphanedChecker = null): iterable
    {
        $items = [];
        $fromIdMap = [];

        foreach ($from as $item) {
            $fromIdMap[$item->getId()] = $item;
        }

        foreach ($to as $item) {
            if ($fromIdMap[$item->getId()]) {
                $items[] = $fromIdMap[$item->getId()];
                unset($fromIdMap[$item->getId()]);
            } else {
                if ($orphanedChecker) {
                    if ($orphanedChecker($item)) {
                        $items[] = $item;
                    }
                } else {
                    $items[] = $item;
                }
            }
        }

        foreach ($fromIdMap as $item) {
            $items[] = $item;
        }

        $this->set($items, $to);

        return $to;
    }

    /**
     * @param iterable<CollectionItemIdentityInterface> $from
     * @param iterable<CollectionItemIdentityInterface> $to
     * @return iterable<CollectionItemIdentityInterface>
     */
    public function set(iterable $from, iterable $to): iterable
    {
        foreach ($to as $key => $value) {
            unset($to[$key]);
        }

        foreach ($from as $key => $item) {
            $to[$key] = $item;
        }

        return $to;
    }
}
