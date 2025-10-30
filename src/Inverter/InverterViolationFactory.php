<?php

namespace Maxkain\EavBundle\Inverter;

use Maxkain\EavBundle\Contracts\Translator\EavTranslatorInterface;

class InverterViolationFactory
{
    public function __construct(
        protected ?EavTranslatorInterface $translator,
    ) {
    }

    function create(string $message, array $parameters = [], array $path = []): EavInverterViolation
    {
        $fullMessage = $message . ' ({path})';
        $compiledPath = implode('.', $path);
        $compiledMessage = $this->compileMessage($message, $parameters);
        $fullCompiledMessage = $compiledMessage . ' (' . $compiledPath . ')';
        $wrappedParameters = [];

        foreach ($parameters as $key => $value) {
            $wrappedParameters['{'. $key . '}'] = $value;
        }

        if ($this->translator) {
            $translatedMessage = $this->translator->trans($message, $wrappedParameters, 'eav');
        } else {
            $translatedMessage = $message;
        }

        $fullTranslatedMessage = $translatedMessage . ' (' . $compiledPath . ')';

        return new EavInverterViolation(
            $message,
            $parameters,
            $path,
            $fullMessage,
            $compiledMessage,
            $fullCompiledMessage,
            $compiledPath,
            $wrappedParameters,
            $translatedMessage,
            $fullTranslatedMessage
        );
    }

    protected function compileMessage(string $message, array $parameters): string
    {
        $params = [];
        foreach ($parameters as $key => $value) {
            $params[] = '{' . $key . '}';
        }

        return str_replace($params, $parameters, $message);
    }
}
