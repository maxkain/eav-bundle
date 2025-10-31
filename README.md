# Eav bundle
>This package is a Symfony bundle, it integrates with Doctrine, EasyAdmin, Symfony Forms and Translator component, and you can use it with REST API. But it has no required dependencies, and you can try to use it with any framework and ORM. In this case, you need to implement some adapters. See `Contracts` and `Bridge` directories.

## Contents
- [Description](#description)
- [Installation](#installation)
- [Creating entities](#creating-entities)
	- [Attribute](#attribute)
	- [EAV](#eav)
	- [Value](#value)
	- [Main entity](#main-entity)
- [EavInverter](#eavinverter)
- [Validation](#validation)
- [EavConverter](#eavconverter)
- [Options registry](#options-registry)
- [EAV Tag](#eav-tag)
- [Query factory](#query-factory)
- [Usage with EasyAdmin and Forms](#usage-with-easyadmin-and-forms)
- [Demo application](https://github.com/maxkain/eav-demo)

## Description

The goal of this bundle is to provide the flexible and high performance implementation of EAV (Entity-Attribute-Value) pattern in PHP. You can give the opportunity to the user of your application creating his own attributes and edit them. 

Main features:

1. Attributes may have any plain type values or enum values, defined by the user. They, also, can be singular or multiple.
2. Binding attributes to one or to many categories or tags. For example, you want to show certain attributes of product only for one or more product categories. Also, you can include attributes of parent categories.
3. One attribute can be associated with many types of entities and tags.
4. Converting and inverting EAV to or from database and client side. Internal input validation.
5. Factory to help you create queries for filtering your entities by attributes with tag bindings checking.
6. Listener, that checks modified tags, attributes, entities and removes orphaned EAVs from database.
7. Ready to use CRUD user interface for EAV, based on Symfony Forms and integrated with EasyAdmin.

## Installation

You need PHP version >= 8.1.

```
composer require maxkain/eav-bundle
```
  
 ## Creating entities
 
For example, you have `App\Entity\Product\Product` entity, and you want to create attribute for it. First, you need to create entities for EAV. It would be nice to create them with Maker bundle, but there is no such functionality for now. Let's create Attribute entity. It can be named as you want, `EnumAttribute`, `StringAttribute`, `MultiEnumAttribute`. But let it be named `MyAttribute`.

### Attribute

```php
namespace App\Entity\Product\Attribute;

use App\Repository\Product\Attribute\MyAttributeRepository;
use Doctrine\ORM\Mapping as ORM;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;

#[ORM\Entity(repositoryClass: MyAttributeRepository::class)]
#[ORM\Table('product_my_attribute')]
class MyAttribute implements EavAttributeInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private string $name;

    public function __toString(): string
    {
        return $this->name;
    }
    
    // ...getters and setters
}
```

Then create entity for EAV.

### EAV

```php
namespace App\Entity\Product\Attribute;

use App\Entity\Product\Product;
use App\Repository\Product\Attribute\MyEavRepository;
use Doctrine\ORM\Mapping as ORM;
use Maxkain\EavBundle\Contracts\Entity\EavAttributeInterface;
use Maxkain\EavBundle\Contracts\Entity\EavInterface;
use Maxkain\EavBundle\Contracts\Entity\EavEntityInterface;

#[ORM\Entity(repositoryClass: MyEavRepository::class)]
#[ORM\Table('product_my_eav')]
#[ORM\UniqueConstraint(fields: ['entity', 'attribute', 'value'])]
class MyEav implements EavInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(Product::class, inversedBy: 'myEavs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Product $entity;

    #[ORM\ManyToOne(MyAttribute::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private MyAttribute $attribute;

    #[ORM\Column]
    private string $value;

    public function __toString(): string
    {
        return $this->attribute->getName();
    }
}
```

There is a `string` value, but it may be any of scalar types. If you want singular values, change `UniqueConstraint` fields to `['entity', 'attribute']`. 

### Value

If you want enum values, you need to create entity for the value field.

```php
namespace App\Entity\Product\Attribute;

use App\Repository\Product\Attribute\MyValueRepository;
use Doctrine\ORM\Mapping as ORM;
use Maxkain\EavBundle\Contracts\Entity\EavValueInterface;

#[ORM\Entity(repositoryClass: MyValueRepository::class)]
#[ORM\Table(name: 'product_my_attribute_value')]
#[ORM\UniqueConstraint(fields: ['attribute', 'title'])]
class MyValue implements EavValueInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(MyAttribute::class, inversedBy: 'values')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private MyAttribute $attribute;

    #[ORM\Column]
    private string $title;

    public function __toString(): string
    {
        return $this->title;
    }
}
```

Then, replace the `value` field in the `MyEav` entity:

```php
#[ORM\ManyToOne(MyValue::class)]
#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
private MyValue $value;
```

And add collection of allowed values to `MyAttribute` entity:

```php
/**
 * @var Collection<MyValue>
 */
#[ORM\OneToMany(MyValue::class, mappedBy: 'attribute', cascade: ['persist'], orphanRemoval: true)]
private Collection $values;
```

Also, you might need adder and remover for this collection, if you use Forms or anything else, which use PropertyAccessor component.

### Main entity

Then, add property to your `Product` entity:

```php
/**
 * @var Collection<MyEav>
 */
#[Assert\Valid]
#[ORM\OneToMany(MyEav::class, 'entity', cascade: ['persist'], orphanRemoval: true)]
private Collection $myEavs;

public function __construct()
{
    $this->myEavs = new ArrayCollection();
}

public function getMyEavs(): Collection
{
    return $this->myEavs;
}
```

Apply changes to your database schema by Doctrine migrations or simply by `bin/console doctrine:schema:update --force` command. Create few attributes in database.

## EavInverter
Now you can change your entity attribute values in your services or controllers by `EavInverter`:

```php
use Maxkain\EavBundle\Inverter\EavInverterInterface;
use Maxkain\EavBundle\Inverter\EavMultipleInputItem;
use Maxkain\EavBundle\Options\EavOptions;
//...

class MyService
{
	public function __construct(
		private EavInverterInterface $eavInverter,
	) {
	}
	
	public myMethod()
	{
		// ...receive your $entity
		
		// then, define your data:
		$myItems = [
			new EavMultipleInputItem(
				attribute: 1, 
				values: ['test1', 'test2']
			),
			new EavMultipleInputItem(
				attribute: 2,
				values: ['test3', 'test4']
			)
		];
				
		// also, you can use arrays:
		$myItems = [
			[
				'attribute': 1, 
				'values': ['test1', 'test2']
			),
			[
				'attribute': 2, 
				'values': ['test3', 'test4']
			]
		];

		// ...or pass value ids or `MyValue` entities, if you use enum values
		
		// set options:
		$options = new EavOptions(
			eavFqcn: MyEav::class,
			entityFqcn: Product::class,
			attributeFqcn: MyAttribute::class,
			valueFqcn: MyValue::class, // if yo have enum value
			multiple: true
		);	

		//	and call the inverter:
		$eavInverter = $this->eavInverter;
		$eavInverter->invert($entity, $myItems, $entity->getMyEavs(), $options);
		if (!$eavInverter->isValid()) {
			$violations = $eavInverter->getViolations();
	
			// ...set the response with violations
			return $response;
		}

		$em->flush();

		// ...set the response
		return $response;
	}
}
```

If you use Forms, the violations will be automatically mapped to form fields. And if you have Translator installed, the messages will be translated.

If you need to add only values, without removal exiting, pass `withAddOnly` parameter to the `invert` function.

If you pass items as arrays, field names can be configured with `reversePropertyMapping` option:

```php
new EavOptions(
	reversePropertyMapping: new ReversePropertyMapping(
		attribute: 'any_attribute_name',
		values: 'any_values_name'
	)
);
```

Also, we used default EAV entities property names. But you can change them by `propertyMapping` option:

```php
new EavOptions(
	propertyMapping: new PropertyMapping(
		entity: 'product',
		entityId: 'guid'
		// and others...
	)
);
```

This mapping is needed for database queries. It would be nice to read options from PHP attributes and reflection at container compile time, but there is no such functionality for now. Although, you will rarely need to change it.

To get the list of allowed attributes for entity, to show it to the user, use `findAllowedAttributes` method of `EavInverter`.

## Validation

You can restrict input types to one of PHP types:

```php
new EavOptions(
	entityInputType: 'integer',
	attributeInputType 'integer',
	valueInputType: 'integer'
);
```

If `ignoreInputEmptyValue` is `false`, then violations will be generated, if value is empty.

## EavConverter

Converts collections of EAV entities to client side. Usage is similar to `EavInverter`.
With `convertItemsToArrays` option you can convert them to arrays or to `EavSingularOutputItem` or `EavMultipleOutputItem` objects .

## Options registry

You can hold your options in one place, registry. Also, registry is used by `OrphanedEavsListener`, which checks and deletes orphaned EAVs, caused by `EavTag` logic.

You need to create configurator:

```php
namespace App\Eav;

class ProductConfigurator implements EavConfiguratorInterface
{
    /**
     * @return array<int|string, EavOptionsInterface>
     */
    public function configure(): array
    {
		return [
			$options = new EavOptions(
				eavFqcn: MyEav::class,
				entityFqcn: Product::class,
				attributeFqcn: MyAttribute::class,
				valueFqcn: MyValue::class, // if yo have enum value
				multiple: true
			)
		];
	}
}
```

Now this options will be loaded by `EavOptionsRegistry`, when it will be created by the service container. Then, you can receive it anywhere:

```php
use Maxkain\EavBundle\Options\EavOptionsRegistry

//...

public function __construct(
	private EavOptionsRegistry $optionsRegistry;
) {
	$options = $optionsRegistry->get(MyEav::class);
	
	// or pass the string as option parameter to inverter or converter
	$eavInverter->invert($entity, $myItems, $entity->getMyEavs(), MyEav::class);
}
```

By default, the key is EAV FQCN, but, if you need to store many options for one EAV, you can define any string key in the array, returned by the configurator. Also, you can clone options to don't repeat them. All these option will be used for checking by EAV tags.

## EAV Tag

Suppose, you have `App\Product\Category` entity. You can bind it to attribute by attribute tag. Create the tag entity:

```php
namespace App\Entity\Product\Attribute;

use App\Entity\Product\Category;
use App\Repository\Product\Attribute\MyTagRepository;
use Doctrine\ORM\Mapping as ORM;
use Maxkain\EavBundle\Contracts\Entity\Tag\EavAttributeTagInterface;

#[ORM\Entity(repositoryClass: MyTagRepository::class)]
#[ORM\Table(name: 'product_my_attribute_tag')]
#[ORM\UniqueConstraint(fields: ['attribute', 'tag'])]
class MyTag implements EavAttributeTagInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(MyAttribute::class, inversedBy: 'tags')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private MyAttribute $attribute;

    #[ORM\ManyToOne(Category::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Category $tag;

    /**
     * @var bool Maybe you want to add any additional properties
     */
    #[ORM\Column]
    private bool $showInFilter = true;
}
```

Then, add it to `MyAttribute` property:

```php
/**
 * @var Collection<MyTag>
 */
#[ORM\OneToMany(MyTag::class, mappedBy: 'attribute', cascade: ['persist'], orphanRemoval: true)]
private Collection $tags;

#[ORM\Column]
private bool $forAllTags = false;
```

Your `Categoty` should  implement `EavTagInterface`, your `Product` should implement `EavEntityWithTagsInterface` and your attribute should implement `EavAttributeWithTagsInterface`. The method `getEavTags` of your `Product` and `MyAttribute` should look, like this:

```php
public function getEavTags(string $tagFqcn): iterable
{
    return match ($tagFqcn) {
        Category::class => isset($this->category) ? [$this->category] : [],
        default => []
    };
}
```

If you have `ManyToMany` Categories, then, like this:

```php
public function getEavTags(string $tagFqcn): iterable
{
    return match ($tagFqcn) {
        Category::class => $this->categories,
        default => []
    };
}
```

`isForAllEavTags` method of attribute looks similar.

And you need to add options:

```php
new EavOptions(
	// ...
    tagFqcn: Category::class,
    attributeTagFqcn: MyTag::class
    propertyMapping: new PropertyMapping( // If you have different name of your category field
    	entityTag: 'category', //for ManyToOne
    	entityTags: 'categories' //for ManyToMany
    	// and see others...
    )
),
```

If you have different properties and different options in your entity with the same category entity, you might set `tagKey` option and pass it value as key of the array, returned by `getEavTags`.

That's all, the `Category` is bound.

If you want to include parent's categories attributes, simply, store them all in a dedicated ManyToMany field of your entity and fill the field by the setter of the main category property or by your service, controller, or by doctrine event listener. And don't forget to set EAV property mapping.

The `OrphanedEavsListener` is enabled by default. It works fast, but you can disable it by `enable_orphaned_eavs_listener` config of the bundle or by `setEnabled` method of the listener.

## Query factory

> Subqueries (semi-joins) are much faster, then joins with group by, if many rows are needed to group.

You may use `EavQueryFactory`, like this:

```php
use Doctrine\ORM\EntityManagerInterface;
use Maxkain\EavBundle\Bridge\Doctrine\EavQueryFactory;
use Maxkain\EavBundle\Query\EavExpression;
use Maxkain\EavBundle\Query\EavComparison;

class MyService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EavQueryFactory $eavQueryFactory
	) {
	}
	
	pubic function myMethod(): array
	{
    	$qb = $this->em->getRepository(Product::class)->createQueryBuilder('e')->select();
        
        $this->eavQueryFactory->addEavFilters($qb, 'e', MyEav::class, [
        	777 => [111, 222],
        	888 => [333, new EavComparison('>', 10)]
        	// ...
        ]);
        
        $this->eavQueryFactory->addEavFilters($qb, 'e', MyAnotherEav::class, [
        	999 => 'myValue1',
        	555 => 'myValue2'
        	111 => new EavComparison('>', 10),
        	333 => new EavComparison('LIKE', 'myValue%'),
        	222 => new EavExpression(':field LIKE '. $qb->createNamedParameter('myValue%'))
        	444 => new EavExpression(':field > 18 AND :field < 30')
        	444 => new EavExpression(':field IN (' . $qb->createNamedParameter([111, 222, 333]) . ')')
        	// ...
        ]);
		
		$result = $qb->getQuery()->getResult();
		
		// ...do something with result 
		return $result;
	}
}

```

Here you pass attributes with values.
If you use `EavExpression`, ':field' placeholder will be replaced to the value field path.
Don't forget to escape user's input by Doctine's `createNamedParameter` function.
If you use `EavComparison`, `value` argument will be escaped automatically.
By default, all conditions use `AND` logic, but with `EavExpression` you may define any DQL condition.

## Usage with EasyAdmin and Forms

You may create CRUD for all entities with EasyAdmin tools. See the [demo application](https://github.com/maxkain/eav-demo). And you can use a `EavCollectionType` form type, which includes the bundle, and it's entry type `EavType`. You may use `EavFieldFactory` to create Easy Admin fields:

```php
namespace App\Controller\Admin\Product;

//...
use Maxkain\EavBundle\Bridge\EasyAdmin\EavFieldFactory;

class ProductCrudController extends AbstractCrudController
{
    public function __construct(
        private EavFieldFactory $eavFieldFactory,
    ) {
    }

    public function configureFields(string $pageName): iterable
    {
        return [
		    $this->eavFieldFactory->create('myEavs', 'My attributes', MyEav::class),
		    $this->eavFieldFactory->create('anotherMyEavs', 'Another attributes', AnotherMyEav::class)
			//...
        ];
	}
}
```

You may use CSS and form theme of the bundle in your controller or dashboard:

```php
public function configureAssets(): Assets
{
    $assets = parent::configureAssets();
    $assets->addCssFile('bundles/maxkaineav/styles/compact_ea_collection.css');

    return $assets;
}

public function configureCrud(): Crud
{
    $crud = parent::configureCrud();
    $crud->setFormThemes(['@Eav/easy-admin/theme.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);

    return $crud;
}
```

Also, you can apply them to any collection field:

```php
CollectionField::new('values')->useEntryCrudForm()->renderExpanded()
    ->addCssClass('compact-ea-collection')
    ->setFormTypeOption('entry_options', [
    	'block_prefix' => 'compact_ea_collection_entry'
	])
```

Errors of the `EavInverter` map to the form correctly. But if you try to validate the value field with Symfony validator by entity attributes, the errors will not be mapped correctly, because the form has another structure. For correctly mapping you may specify constraints directly in form entry options:

```php
$this->eavFieldFactory->create('myEavs', null, MyEav::class, null, [], [
	EavType::VALUE_CONSTRAINTS => [new Assert\Email()]
])
```

And there are other options, you can pass.

If you use EAV tag, the tag field should be earlier, then EAV properties, in the fields order. This is necessary for tags checker could read this field.
