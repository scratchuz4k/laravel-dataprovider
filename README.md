# Laravel Dataprovider

This package allows you to filter, sort and include eloquent relations based on a request. The `DataProvider` used in this package uses Laravel's default Eloquent builder and the Illuminate Request.

## Basic usage

Just intanciate the provider and return its get method and you're good to go.

```php
use Scratchuz4k\Laravel\DataProvider;

$dataProvider = new DataProvider(Article::class);
return $dataProvider->get($request);
```

## Available operators

- equals
- not_equals
- contains
- starts_with
- ends_with
- greater_than
- less_than
- greater_than_or_equal_to
- less_than_or_equal_to
- is_null

## Queries Examples

- Basic filter:

```sh
/articles?
filters[0][column]=id&
filters[0][operator]=equals&
filters[0][value]=1
```

- Or filter:

```sh
/articles?
filters[0][column]=id&
filters[0][operator]=equals&
filters[0][value]=1&

filters[1][column]=id&
filters[1][operator]=equals&
filters[1][value]=2&
filters[1][function]=orWhere
```

- Including relations:

```sh
/article?
with=categories
```

The relations included should be the relations present in the Model.

- Sorting a query:

```sh
/articles?
sortOrder=asc&
sortBy=id
```

- Filter relations:

```sh
/articles?
filters[0][column]=categories.id&
filters[0][operator]=equals&
filters[0][value]=1
```

The relations filtered should be the relations present in the Model.
