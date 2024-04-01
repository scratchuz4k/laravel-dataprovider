# Laravel Dataprovider

This package allows you to filter, sort and include eloquent relations based on a request. The `DataProvider` used in this package uses Laravel's default Eloquent builder and the Illuminate Request.

## Basic usage

Just intanciate the provider and return its get method and you're good to go.

```php
use Scratchuz4k\LaravelDataprovider\DataProvider;

$dataProvider = new DataProvider(Article::class);
return $dataProvider->get($request);
```

## Available queries

- Basic filter:

```sh
/articles?
filters[0][column]=id&
filters[0][operator]=equals&
filters[0][value]=1
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
