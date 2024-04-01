# Laravel Dataprovider

This package allows you to filter, sort and include eloquent relations based on a request. The `DataProvider` used in this package uses Laravel's default Eloquent builder.

## Basic usage

### Filter a query based on a request:

`/articles?filters[0][column]=id&filters[0][operator]=equals&filters[0][value]=1`

```php
use Scratchuz4k\LaravelDataprovider\DataProvider;

$dataProvider = new DataProvider(Article::class);
return $dataProvider->get($request);
```

### Including relations based on a request: 

`/articles?with=categories`

The relations included should be the relations present in the Model.

```php

use Scratchuz4k\LaravelDataprovider\DataProvider;

$dataProvider = new DataProvider(Article::class);
return $dataProvider->get($request);

// all `Articles`s with their `Categories` loaded
```
### Sorting a query based on a request: 

`/users?sortOrder=asc&sortBy=id`