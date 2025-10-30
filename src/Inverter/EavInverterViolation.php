<?php

namespace Maxkain\EavBundle\Inverter;

class EavInverterViolation
{
    public function __construct(
        protected string $message,
        protected array $parameters,
        protected array $path,
        protected string $debugMessage,
        protected string $compiledMessage,
        protected string $debugCompiledMessage,
        protected string $compiledPath,
        protected array $wrappedParameters,
        protected string $translatedMessage,
        protected string $debugTranslatedMessage
    ) {
    }

    public function __toString(): string
    {
        return $this->translatedMessage;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDebugMessage(): string
    {
        return $this->debugMessage;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getWrappedParameters(): array
    {
        return $this->wrappedParameters;
    }

    public function getCompiledMessage(): string
    {
        return $this->compiledMessage;
    }

    public function getDebugCompiledMessage(): string
    {
        return $this->debugCompiledMessage;
    }

    public function getCompiledPath(): string
    {
        return $this->compiledPath;
    }

    public function getPath(): array
    {
        return $this->path;
    }

    public function getTranslatedMessage(): string
    {
        return $this->translatedMessage;
    }

    public function getDebugTranslatedMessage(): string
    {
        return $this->debugTranslatedMessage;
    }
}
