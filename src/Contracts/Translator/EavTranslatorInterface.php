<?php

namespace Maxkain\EavBundle\Contracts\Translator;

/**
 * Optional
 */
interface EavTranslatorInterface
{
    public function trans(?string $id, array $parameters = [], ?string $domain = null): string;
}
