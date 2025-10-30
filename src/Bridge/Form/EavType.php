<?php

namespace Maxkain\EavBundle\Bridge\Form;

use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;
use Maxkain\EavBundle\Contracts\Repository\EavRepositoryInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EavType extends AbstractType
{
    public const EAV_OPTIONS = 'eav_options';
    public const VALUE_TYPE = 'value_type';
    public const VALUES_TYPE = 'values_type';
    public const VALUE_OPTIONS = 'value_options';
    public const VALUE_CONSTRAINTS = 'value_constraints';
    public const EA_AUTOCOMPLETE = 'ea';

    use EavOptionsTrait;

    protected mixed $data;

    public function __construct(
        protected EavRepositoryInterface $repository,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::EAV_OPTIONS => null,
            self::VALUE_TYPE => null,
            self::VALUES_TYPE => null,
            self::VALUE_OPTIONS => [],
            self::VALUE_CONSTRAINTS => [],
            self::EA_AUTOCOMPLETE => false,
        ])->setRequired([self::EAV_OPTIONS])
        ->setAllowedTypes(self::EAV_OPTIONS, EavOptionsInterface::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->options = $options;
        $eavOptions = $this->getEavOptions();
        $reverseMapping = $eavOptions->getReversePropertyMapping();

        $builder
            ->addEventListener(FormEvents::SUBMIT, [$this, 'submit'])
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);

        $builder->add($reverseMapping->getAttribute(), HiddenType::class);

        $builder->get($reverseMapping->getAttribute())
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postAttributeSubmit']);
    }

    protected function createValuesForm(FormInterface $form): void
    {
        $eavOptions = $this->getEavOptions();
        $reverseMapping = $eavOptions->getReversePropertyMapping();
        $isEnum = (bool) $eavOptions->getValueFqcn();
        $choices = [];
        $attribute = null;
        $attributeName = null;

        if (isset($this->data)) {
            $attribute = $this->data[$reverseMapping->getAttribute()];
            $attributeName = $this->data[$reverseMapping->getAttributeName()];
        }

        if ($isEnum && $attribute) {
            $choices = $this->getValueChoices($attribute);
        }

        if ($eavOptions->isMultiple()) {
            if ($isEnum) {
                $this->addEnumValuesForm($form, $reverseMapping->getValues(), $attributeName, $choices);
            } else {
                $this->addValuesForm($form, $reverseMapping->getValues(), $attributeName);
            }
        } else {
            if ($isEnum) {
                $this->addEnumValueForm($form, $reverseMapping->getValue(), $attributeName, $choices);
            } else {
                $this->addValueForm($form, $reverseMapping->getValue(), $attributeName);
            }
        }

        $this->extractEa($form);
    }

    protected function addValueForm(FormInterface $form, string $name, ?string $attributeName): void
    {
        $form->add($name, $this->getValueType() ?? TextType::class, array_merge_recursive([
            'label' => $attributeName,
            'constraints' => $this->getValueConstraints(),
        ], $this->getValueOptions()));
    }

    protected function addValuesForm(FormInterface $form, string $name, ?string $attributeName): void
    {
        $blockPrefix = $form->getParent()->getConfig()->getOption('entry_options')['block_prefix'];

        $form->add($name, $this->getValuesType() ?? CollectionType::class, array_merge_recursive([
            'entry_type' => $this->getValueType() ?? TextType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'label' => $attributeName,
            'entry_options' => [
                'block_prefix' => $blockPrefix,
                'constraints' => $this->getValueConstraints()
            ],
        ], $this->getValueOptions()));
    }

    protected function addEnumValueForm(FormInterface $form, string $name, ?string $attributeName, array $choices): void
    {
        $eaOptions = $this->isEaAutocomplete() ? [
            'attr' => ['data-ea-widget' => 'ea-autocomplete']
        ] : [];

        $form->add($name, $this->getValueType() ?? ChoiceType::class, array_merge_recursive([
            'choices' => $choices,
            'expanded' => false,
            'multiple' => false,
            'label' => $attributeName,
            'required' => false,
            'constraints' => $this->getValueConstraints(),
        ], $eaOptions, $this->getValueOptions()));
    }

    protected function addEnumValuesForm(FormInterface $form, string $name, ?string $attributeName, array $choices): void
    {
        $eaOptions = $this->isEaAutocomplete() ? [
            'attr' => ['data-ea-widget' => 'ea-autocomplete']
        ] : [];

        $form->add($name, $this->getValueType() ?? ChoiceType::class, array_merge_recursive([
            'choices' => $choices,
            'multiple' => true,
            'expanded' => false,
            'label' => $attributeName,
            'constraints' => $this->getValueConstraints(),
        ], $eaOptions, $this->getValueOptions()));
    }

    public function submit(SubmitEvent $event): void
    {
        $this->options = $event->getForm()->getConfig()->getOptions();
        $this->data = $event->getForm()->getData();
    }

    public function postAttributeSubmit(PostSubmitEvent $event): void
    {
        $this->options = $event->getForm()->getParent()->getConfig()->getOptions();
        $this->data = $event->getForm()->getParent()->getData();
        $this->createValuesForm($event->getForm()->getParent());
    }

    public function preSetData(PreSetDataEvent $event): void
    {
        $this->options = $event->getForm()->getConfig()->getOptions();
        $this->data = $event->getData();
        $this->createValuesForm($event->getForm());
    }

    protected function getValueChoices(mixed $attribute): array
    {
        $values = $this->findValues($attribute);
        $choices = [];
        foreach ($values as $value) {
            $choices[$value->getTitle()] = $value->getId();
        }

        return $choices;
    }

    /**
     * @return array<EavValueInterface>
     */
    protected function findValues(mixed $attributeId): array
    {
        $mapping = $this->getEavOptions()->getPropertyMapping();
        $valueFqcn = $this->getEavOptions()->getValueFqcn();

        return $this->repository->findBy($valueFqcn, [$mapping->getValueAttribute() => $attributeId]);
    }

    protected function extractEa(FormInterface $form): void
    {
        if (!class_exists(FieldDto::class)) {
            return;
        }

        $eaField = $form->getParent()->getConfig()->getAttribute('ea_field');
        $eavOptions = $this->getEavOptions();
        $reverseMapping = $eavOptions->getReversePropertyMapping();
        $valuesForm = $form->get($eavOptions->isMultiple()
            ? $reverseMapping->getValues()
            : $reverseMapping->getValue());
        $attributes = $valuesForm->getConfig()->getAttributes();
        $attributes['ea_field'] = $eaField;
        $ref = new \ReflectionClass(FormConfigBuilder::class);
        $prop = $ref->getProperty('attributes');
        $prop->setAccessible(true);
        $prop->setValue($valuesForm->getConfig(), $attributes);
    }
}
