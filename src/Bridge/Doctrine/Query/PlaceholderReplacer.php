<?php

namespace Maxkain\EavBundle\Bridge\Doctrine\Query;

class PlaceholderReplacer
{
    public function replace(array|string $search, array|string $replace, string $subject, string $prefix = ':'): string
    {
        $wordEndPattern = '(\W|$)';
        $items = $this->prepareItems($search, $replace, $prefix);
        $replacedSubject = $subject;
        foreach ($items as $s => $r) {
            $replacedSubject = preg_replace("/$s($wordEndPattern)/", $r . '$1', $replacedSubject);
        }

        return $replacedSubject;
    }

    protected function prepareItems(array|string $search, array|string $replace, string $prefix): array
    {
        $search = is_array($search) ? $search : [$search];
        $replace = is_array($replace) ? $replace : [$replace];
        $items = [];
        $i = 0;
        foreach ($search as $item) {
            $items[$prefix . $item] = $replace[$i];
            $i++;
        }

        return $items;
    }
}
