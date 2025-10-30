<?php

namespace Maxkain\EavBundle\Utils\CollectionSetter;

class CollectionSetter
{
    /**
     * @param array<CollectionItemIdentityInterface> $from
     * @param iterable<CollectionItemIdentityInterface> $to
     * @return iterable<CollectionItemIdentityInterface>
     */
    public function add(array $from, iterable $to): iterable
    {
        $items = [];
        $fromIdMap = [];

        foreach ($from as $item) {
            $fromIdMap[$item->getId()] = $item;
        }

        foreach ($to as $item) {
            $items[] = $fromIdMap[$item->getId()] ?? $item;
            unset($fromIdMap[$item->getId()]);
        }

        foreach ($fromIdMap as $item) {
            $items[] = $item;
        }

        $this->set($items, $to);

        return $to;
    }

    /**
     * @param array<CollectionItemIdentityInterface> $from
     * @param iterable<CollectionItemIdentityInterface> $to
     * @return iterable<CollectionItemIdentityInterface>
     */
    public function set(array $from, iterable $to): iterable
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
