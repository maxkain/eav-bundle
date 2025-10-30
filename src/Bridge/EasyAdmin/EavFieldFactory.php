<?php

namespace Maxkain\EavBundle\Bridge\EasyAdmin;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Maxkain\EavBundle\Bridge\Form\EavCollectionType;
use Maxkain\EavBundle\Bridge\Form\EavType;
use Maxkain\EavBundle\Options\EavOptions;
use Maxkain\EavBundle\Options\EavOptionsRegistry;

final class EavFieldFactory
{
    public function __construct(
        private EavOptionsRegistry $optionsRegistry
    ) {
    }

    public function create(
        string $name,
        mixed $label,
        EavOptions|string $eavOptions,
        ?string $formType = null,
        array $formOptions = [],
        array $formEntryOptions = []
    ): FieldInterface {
        if (is_string($eavOptions)) {
            $eavOptions = $this->optionsRegistry->get($eavOptions);
        }

        $eavOptions->setConvertItemsToArrays(true);

        $formType = $formType ?? EavCollectionType::class;
        $defaultOptions = [
            'entry_options' => [
                'block_prefix' => 'compact_ea_collection_entry',
                EavType::EA_AUTOCOMPLETE => true,
                EavType::EAV_OPTIONS => $eavOptions
            ]
        ];

        $resultOptions = array_merge_recursive($defaultOptions, $formOptions, ['entry_options' => $formEntryOptions]);

        return CollectionField::new($name, $label)
            ->addCssClass('compact-ea-collection')
            ->setEntryToStringMethod(fn() => false)
            ->allowAdd(false)
            ->allowDelete(false)
            ->setFormType($formType)
            ->setFormTypeOptions($resultOptions)
            ->renderExpanded()
            ->setCustomOption('crossCloseButton', false)
            ->setEntryIsComplex();
    }
}
