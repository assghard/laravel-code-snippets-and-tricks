# Laravel Enums (Leverage Enums for attribute casting)

Eloquent allows you to cast your attribute values to PHP Enums. Enums is a new feature in PHP 8+ which is supporting from Laravel 9.
Instead of creating enum column in database you can do Enum class and use it in Model in casts array.

```php
    <?php

    /**
     * This approach allows to have control for Enums from the code. 
     * In databases there are only numbers stored in `status` column, but in code you use case names. 
     * Everything related to Post status is in one class (Solid)
     */

    declare(strict_types=1);

    namespace App\Enums;

    use App\Traits\BaseEnumTrait;

    enum PostStatusEnum: int
    {
        use BaseEnumTrait;

        case Draft = 1;
        case Published = 2;
        case Inactive = 3;

        public function label(): string
        {
            return __('post.statuses.'.$this->name);
        }

        public function badge(): string
        {
            return __('post_badges.'.$this->name);
        }

        // additional methods below

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

## Enums usage

### Enum values

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

### Translations, labels, badges

```php
// lang/en/post.php
return [
    'statuses' => [
        'Draft' => 'Draft',
        'Published' => 'Published',
        'Inactive' => 'Inactive'
    ],
];

// lang/pl/post.php
return [
    'statuses' => [
        'Draft' => 'Szkic',
        'Published' => 'Opublikowany',
        'Inactive' => 'Nie aktywny'
    ],
];

// lang/OTHER_LANG/post.php
...


// lang/en/post_badges.php => Bootstrap badge class. Add only en version as `fallback_locale` is en
return [
    'Draft' => 'secondary',
    'Published' => 'success',
    'Inactive' => 'danger'
];

```

### Leverage enum for Post status casting

```php
    ...
    use App\Enums\PostStatusEnum;
    ...

    class Post extends Model
    {
        protected $table = 'blog__posts';
        protected $fillable = [
            'title',
            'body',
            'status'
        ];

        /**
         * The attributes that should be cast.
         *
         * @var array<string, string>
         */
        protected $casts = [
            'status' => PostStatusEnum::class, // leverage enums for status casting
        ];

        ...
```


### Enum methods

```php
    $post->isPublished() // true | false

    if (!$post->isPublished()) {
        abort(404);
    }

    $post->update(['status' => PostStatusEnum::Inactive]); // OK
    $post->update(['status' => 3]); // OK

    $post->update(['status' => 100]); // Error


    if ($post->status == PostStatusEnum::Inactive) {
        # code here
    }

    if (PostStatusEnum::Inactive->value === 3) {
        # code here
    }
```