<?php

namespace Maxkain\EavBundle\Options;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class EavOptionsRegistry implements EavOptionsResolverInterface
{
    protected int $index = 0;

    /** @var array<string, EavOptionsInterface> */
    protected array $options = [];

    /** @var array<string, array<string, EavOptionsInterface>> */
    protected array $optionsByAttribute = [];

    /** @var array<string, array<string, EavOptionsInterface>> */
    protected array $optionsByEntity = [];

    /** @var array<string, array<string, EavOptionsInterface>> */
    protected array $optionsByEav = [];

    /** @var array<string, array<string, EavOptionsInterface>> */
    protected array $optionsByTag = [];

    /**
     * @param iterable<EavConfiguratorInterface> $configurators
     */
    public function __construct(
        #[AutowireIterator('eav.configurator')]
        protected iterable $configurators,
    ) {
        $this->load();
    }

    public function load(): void
    {
        foreach ($this->configurators as $configurator) {
            foreach ($configurator->configure() as $key => $config) {
                $eavKey = is_string($key) ? $key : null;
                $this->put($config, $eavKey);
            }
        }
    }

    public function put(EavOptionsInterface $options, ?string $eavKey = null, bool $checkExistence = true): EavOptionsInterface
    {
        $eavKey = $eavKey ?? $options->getEavFqcn();
        if ($checkExistence && isset($this->options[$eavKey])) {
            throw new EavOptionsRegistryException('Registry key already exists.');
        }

        if ($options instanceof SetIndexInterface) {
            $options->setIndex($this->index);
        }

        $this->index++;
        $this->options[$eavKey] = $options;
        $this->optionsByAttribute[$options->getAttributeFqcn()][$eavKey] = $options;
        $this->optionsByEntity[$options->getEntityFqcn()][$eavKey] = $options;
        $this->optionsByEav[$options->getEavFqcn()][$eavKey] = $options;
        foreach ($options->getTagOptions() as $tagOption) {
            $this->optionsByTag[$tagOption->getTagFqcn()][$eavKey] = $options;
        }

        return $options;
    }

    public function get(string $eavKey): ?EavOptionsInterface
    {
        return $this->options[$eavKey] ?? null;
    }

    /**
     * @param EavOptionsInterface|string $options Object or eavFqcn or eavKey
     */
    public function resolve(EavOptionsInterface|string $options): EavOptionsInterface
    {
        $options = is_string($options) ? $this->get($options) : $options;
        if (!$options) {
            throw new EavOptionsRegistryException('Options not found.');
        }

        return $options;
    }

    /**
     * @return array<string, EavOptionsInterface>
     */
    public function getAll(): array
    {
        return $this->options;
    }

    /**
     * @return array<string, EavOptionsInterface>
     */
    public function getByAttribute(string $attributeFqcn): array
    {
        return $this->optionsByAttribute[$attributeFqcn] ?? [];
    }

    /**
     * @return array<string, EavOptionsInterface>
     */
    public function getByEntity(string $entityFqcn): array
    {
        return $this->optionsByEntity[$entityFqcn] ?? [];
    }

    /**
     * @return array<string, EavOptionsInterface>
     */
    public function getByEav(string $eavFqcn): array
    {
        return $this->optionsByEav[$eavFqcn] ?? [];
    }

    /**
     * @return array<string, EavOptionsInterface>
     */
    public function getByTag(string $tagFqcn): array
    {
        return $this->optionsByTag[$tagFqcn] ?? [];
    }

    /**
     * @return array<string>
     */
    public function getAllEavFqcns(): array
    {
        return array_keys($this->optionsByEav);
    }
}
