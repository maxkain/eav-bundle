<?php

namespace Maxkain\EavBundle\Bridge\Form;

use Maxkain\EavBundle\Contracts\Entity\Tag\EavTagInterface;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Contracts\Entity\EavEntityInterface;
use Maxkain\EavBundle\Converter\EavConverterInterface;
use Maxkain\EavBundle\Inverter\EavInverterInterface;
use Maxkain\EavBundle\Options\EavOptionsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Event\PostSetDataEvent;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class EavCollectionType extends AbstractType
{
    protected array $options;

    /**
     * @var iterable<EavInterface>
     */
    protected iterable $data;
    protected FormInterface $form;

    public function __construct(
        protected EavConverterInterface $eavConverter,
        protected EavInverterInterface $eavInverter,
        protected PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => EavType::class,
            'setter' => $this->setter(...),
            'error_bubbling' => false,
            'allow_add' => false,
            'allow_delete' => false
        ]);
    }

    protected function getEavOptions(): EavOptionsInterface
    {
        return $this->options['entry_options'][EavType::EAV_OPTIONS];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->options = $options;

        $builder
            ->addEventListener(FormEvents::SUBMIT, [$this, 'submit'])
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData'])
            ->addEventListener(FormEvents::POST_SET_DATA, [$this, 'postSetData'])
            ->addModelTransformer(new CallbackTransformer($this->transform(...), $this->reverseTransform(...)));
    }

    public function transform(iterable $eavs): array
    {
        return $this->eavConverter->convert($eavs, $this->getEavOptions());
    }

    public function reverseTransform(array $items): iterable
    {
        /** @var EavEntityInterface $entity */
        $entity = $this->form->getParent()->getData();
        $this->updateTag();
        $this->eavInverter->invert($entity, $items, $this->data, $this->getEavOptions());
        $this->addInverseErrors();

        return $this->data;
    }

    protected function updateTag(): void
    {
        $parentForm = $this->form->getParent();

        /** @var EavEntityInterface $entity */
        $entity = $parentForm->getData();

        foreach ($parentForm->all() as $childForm) {
            $propertyPath = $childForm->getPropertyPath();
            $config = $childForm->getConfig();
            if ($config->hasOption('class')
                && $this->classImplements($config->getOption('class'), EavTagInterface::class)
                && $this->propertyAccessor->isWritable($entity, $propertyPath)
            ) {
                $this->propertyAccessor->setValue($entity, $propertyPath, $childForm->getData());
                return;
            }
        }
    }

    protected function classImplements(string $class, string $interface): bool
    {
        $interfaces = class_implements($class);
        if ($interfaces && in_array($interface, $interfaces)) {
            return true;
        }

        return false;
    }

    protected function addInverseErrors(): void
    {
        $options = $this->getEavOptions();
        $reverseMapping = $options->getReversePropertyMapping();
        $violations = $this->eavInverter->getViolations();

        foreach ($violations as $violation) {
            $form = $this->form;
            $path = null;
            foreach ($violation->getPath() as $path) {
                if ($form->has($path)) {
                    $form = $form->get($path);
                }
            }

            if ($path == $reverseMapping->getAttribute()) {
                $form = $form->getParent();
                $valueProperty =  $options->isMultiple() ? $reverseMapping->getValues() : $reverseMapping->getValue();
                $form = $form->get($valueProperty);
                if ($options->isMultiple() && !$options->getValueFqcn()) {
                    foreach ($form->all() as $childForm) {
                        $childForm->addError(new FormError($violation->getTranslatedMessage()));
                    }
                } else {
                    $form->addError(new FormError($violation->getTranslatedMessage()));
                }
            } else {
                $form->addError(new FormError($violation->getTranslatedMessage()));
            }
        }
    }

    public function submit(SubmitEvent $event): void
    {
        $this->options = $event->getForm()->getConfig()->getOptions();
        $this->form = $event->getForm();
        $this->data = $event->getForm()->getData();
    }

    public function preSetData(PreSetDataEvent $event): void
    {
        $this->options = $event->getForm()->getConfig()->getOptions();
        $this->form = $event->getForm();
        $this->data = $event->getData();
        $this->fillData();
    }

    public function postSetData(PostSetDataEvent $event): void
    {
        $this->options = $event->getForm()->getConfig()->getOptions();
        $this->form = $event->getForm();
        $this->data = $event->getData();
        $this->resizeCollection();
    }

    protected function fillData(): void
    {
        $data = $this->data;

        /** @var EavEntityInterface $entity */
        $entity = $this->form->getParent()->getData();

        $attributeKeys = [];
        foreach ($data as $eav) {
            $attributeId = $eav->getAttribute()->getId();
            $attributeKeys[$attributeId] = null;
        }

        $allowedAttributesKeys = [];
        $attributes = $this->eavInverter->findAllowedAttributes($entity, $this->getEavOptions());
        foreach ($attributes as $attribute) {
            $attributeId = $attribute->getId();
            $allowedAttributesKeys[$attributeId] = null;
            if (!array_key_exists($attributeId, $attributeKeys)) {
                $data = $this->createEav();
                $data->setEntity($entity);
                $data->setAttribute($attribute);
                $this->data[] = $data;
                $attributeKeys[$attributeId] = $attributeId;
            }
        }

        foreach ($this->data as $key => $eav) {
            if (!array_key_exists($eav->getAttribute()->getId(), $allowedAttributesKeys)) {
                unset($this->data[$key]);
            }
        }
    }

    protected function createEav(): EavInterface
    {
        return new ($this->getEavOptions()->getEavFqcn());
    }

    protected function resizeCollection(): void
    {
        $form = $this->form;
        foreach ($form as $name => $child) {
            $form->remove($name);
        }

        $attributeKeys = [];
        foreach ($this->data as $eav) {
            $attributeId = $eav->getAttribute()->getId();
            $attributeKeys[$attributeId] = 1;
        }

        $attributeKeys = array_values($attributeKeys);

        $options = $this->options;
        $prototypeOptions = $options['entry_options'];
        if ($options['allow_add'] && $options['prototype']) {
            $prototypeOptions = array_replace($options['entry_options'], $options['prototype_options']);
        }

        foreach ($attributeKeys as $name => $value) {
            $form->add($name, $options['entry_type'], array_replace([
                'property_path' => '['.$name.']',
            ], $prototypeOptions));
        }
    }

    public function setter(object $entity, iterable $eavs, FormInterface $form): void
    {
        $collection = $this->propertyAccessor->getValue($entity, $form->getPropertyPath());
        foreach ($collection as $key => $item) {
            unset($collection[$key]);
        }

        foreach ($eavs as $key => $eav) {
            $collection[$key] = $eav;
        }
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
