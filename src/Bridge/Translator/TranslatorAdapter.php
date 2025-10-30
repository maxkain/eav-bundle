<?php

namespace Maxkain\EavBundle\Bridge\Translator;

use Maxkain\EavBundle\Contracts\Translator\EavTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatorAdapter implements EavTranslatorInterface
{
    public function __construct(
        private ?TranslatorInterface $translator,
    ) {
    }

    public function trans(?string $id, array $parameters = [], ?string $domain = null): string
    {
        if ($this->translator) {
            return $this->translator->trans($id, $parameters, $domain);
        }

        return $id;
    }
}
