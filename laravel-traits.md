# Laravel traits

[Go to main README](README.md)

- [What is trait and how to use it in Laravel](#what-is-trait-in-php-and-how-to-use-it-in-laravel)
- [Sluggable trait for Laravel project](#sluggable-trait-for-laravel-project)
- [UsesUuid trait for Laravel project](#usesuuid-trait-for-laravel-project)
- [Searchable trait for Laravel project](#searchable-trait-for-laravel-project)
- [BaseEnumTrait trait for Laravel Enums](#baseenumtrait-trait-for-laravel-enums)

## What is trait in PHP and how to use it in Laravel
Trait is a mechanism for code reuse that allows developers to share methods and properties among classes without using inheritance. Traits can be thought of as a set of methods that can be included in a class, similar to how you might include a library in your code. 

**Traits provide multiple inheritance in PHP and code reusing**.

## Sluggable trait for Laravel project

Generate `slug` (uri) for your model from certain field. The field can be specified in model

### Trait contains:
* Mechanism of generating slug from certain field on model `creating` and `updating` events
* `sluggableSource()` method to set slug source field
* Two static methods: `findBySlug($slug);` and `findOrFailBySlug($slug);`
* `createSlug()` method

### Trait Usage
1. Add trait to your project: [Sluggable trait implementation](/Traits/Sluggable.php)
2. Your model and table shoud contains field `slug`, so add field to migration `$table->string('slug')->unique();` and `$fillable` filed
3. In model add trait and use it: 

```php
declare(strict_types=1);

...
use App\Traits\Sluggable;

class Page extends Model
{
    use Sluggable;

    protected $table = 'pages';
    protected $fillable = [
        'title',
        'slug',
        'body'
    ];
    ...

    /**
     * Always make slug from title
     */
    public function sluggableSource(): string
    {
        return 'title';
    }
```

That's it. On model `creating` and `updating` trait will generate a `slug` from `title`. 
Also there are two static methods added in trait: 

```php
    public static function findBySlug(string $slug)
    {
        return self::where('slug', $slug)->first();
    }

    public static function findOrFailBySlug(string $slug)
    {
        return self::where('slug', $slug)->firstOrFail();
    }
```

so you can use them:

```php
    # in controller
    public function subpage(string $slug)
    {
        $page = Page::findOrFailBySlug($slug);

        return view('pages.subpage', compact('page'));
    }

    # in service
    $page = Page::findBySlug($slug);
```

## UsesUuid trait for Laravel project

`UsesUuid` trait provides UUID generating for Laravel application

### Trait contains:
* Mechanism of generating UUID for Model on `creating` event
* `uuidFieldName()` method to set `uuid` field from Model
* Two static methods: `findByUuid(string $uuid, array $joins = []);` and `findOrFailByUuid(string $uuid, array $joins = []);`

### Trait usage
1. Add trait to your project (example: to `app/Traits` directory): [UsesUuid trait implementation](/Traits/UsesUuid.php)
2. Add `uuid` field to your migration: 

```php
    ...
    $table->uuid('uuid')->unique();
    ...
```

3. In your Model: 

```php

    ...

    use App\Traits\UsesUuid; // <-- Add trait
    ...

    # Order is just an example
    class Order extends Model
    {
        ...
        use UsesUuid; // <-- use trait
        ...

        protected $table = 'orders';
        protected $fillable = [
            ...
            'customer_id',
            'state',
            'type',
            'uuid', // <-- Add uuid as fillable field
            ...
        ];

        ...

    public function uuidFieldName(): string
    {
        return 'uuid'; // <-- Set UUID field name to trait
    }
```

4. In Controller/Service

```php
    $order = Order::create([
        'customer_id' => auth()->id(),
    ]);

    dd($order->uuid); // 423606k2-622d-45db-a3fi-1753dc13c337 <-- UUID will be generated by trait automatically on model `creating` event and saved to database to `uuid` column


    # Find Order by UUID
    $customerOrder = Order::findOrFailByUuid($uuid);
    $customerOrder = Order::findByUuid($uuid);

    # Find Order by UUID with joins
    $customerOrder = Order::findByUuid($uuid, ['products', 'customer', 'customer.invoice_data']);
```

## Searchable trait for Laravel project

`Searchable` trait for quick searching implementation by certain fields and relationships. 
Trait uses "%LIKE%" statements for `searchableFields()` fields. 

**Traits works well with pagination, global and local scopes, relationships**

### Trait contains:
* `searchableFields()` method to set searchable fields from Model
* `scopeSearch()` local scope which provides model searching in searchable fields by phrase

### Trait usage

Add trait to your project: [Searchable trait implementation](/Traits/Searchable.php)

```php
    ...
    # add use
    use App\Traits\Searchable;
    ...

    class Page extends Model
    {
        use Searchable; // <-- use trait

        protected $table = 'pages';
        protected $fillable = [
            'title',
            'body'
        ];

        # Set searchable fields
        public function searchableFields(): array
        {
            return [
                # search by model fields
                'title',
                'body',

                # search by relationships
                'tags' => ['name', 'description'] // <-- search by related Tag model name
            ];
        }

        public function tags(): HasMany
        {
            return $this->hasMany(Tag::class);
        }
```

```php
    # Example 1. Find pages using `search()` method in controller/service

    $page = Page::search('PHRASE_HERE')->first();

    $pagesCollection = Page::with(['tags'])->search('PHRASE_HERE')->get();

    $pagesPaginated = Page::with(['tags'])->search('PHRASE_HERE')->paginate(10);


    # Example 2. Search by related model 
    $pages = Page::with(['tags'])->whereHas('tags', function ($q) use ($phrase) {
        $q->search($phrase);
    })->get();
```

## BaseEnumTrait trait for Laravel Enums

Trait adds some simple and useful functions to Laravel Enums

Add trait to your project: [BaseEnumTrait trait implementation](/Traits/BaseEnumTrait.php)

```php
    declare(strict_types=1);

    namespace App\Enums;

    use App\Traits\BaseEnumTrait;

    enum PostStatusEnum: int
    {
        use BaseEnumTrait; // <-- use trait

        case Draft = 1;
        case Published = 2;
        case Inactive = 3;

        public function label(): string
        {
            return __('post.statuses.'.$this->name);
        }

        public function isDraft(): bool
        {
            return $this === self::Draft;
        }

        public function isPublished(): bool
        {
            return $this === self::Published;
        }

        public function isInactive(): bool
        {
            return $this === self::Inactive;
        }
    }
```

```php
    $cases = PostStatusEnum::cases();
    // [
    //     App\Enums\PostStatusEnum {
    //         +name: "Draft",
    //         +value: 1,
    //     },
    //     App\Enums\PostStatusEnum {
    //         +name: "Published",
    //         +value: 2,
    //     },
    //     App\Enums\PostStatusEnum {
    //         +name: "Inactive",
    //         +value: 3,
    //     }
    // ]


    $names = PostStatusEnum::names();
    // [
    //     "Draft",
    //     "Published",
    //     "Inactive"
    // ]


    $values = PostStatusEnum::values();
    // [
    //     1,
    //     2,
    //     3
    // ]


    $valuesToNameArray = PostStatusEnum::array();
    // [
    //     1 => "Draft",
    //     2 => "Published",
    //     3 => "Inactive"
    // ]
```