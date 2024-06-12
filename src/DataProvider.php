<?php

namespace App\Helpers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DataProvider
{
    public $model;

    public $builder;

    public string $orderBy = 'id';

    public string $order = 'desc';

    protected array $relations = [];

    public function __construct($model)
    {
        $this->model = app($model);
        $this->builder = app($model);
    }

    public function with(array $items)
    {
        array_push($this->relations, ...$items);

        return $this;
    }

    public function get(Request $request)
    {
        if ($request->has('tree')) {
            $this->builder = $this->model::tree();
        }

        $this->builder = $this->builder->with($this->relations);

        if ($request->has('with')) {
            $this->builder = $this->builder->with($request->get('with'));
        }

        $limit = $request->limit ?? 20;
        $page = $request->page ?? 1;
        $order = $request->has('sortOrder') && $request->sortOrder ? $request->sortOrder : $this->order;
        $orderBy = $request->has('sortBy') && $request->sortBy ? $request->sortBy : $this->orderBy;

        if ($request->has('filters') && count($request->filters)) {
            $this->buildSearch($request->filters);
        }

        try {
            return $this->builder->orderBy($orderBy, $order)->paginate(perPage: $limit, page: $page);
        } catch (QueryException $e) {
            return $e->getMessage();
        }
    }

    public function buildSearch($filters)
    {
        foreach ($filters as $filter) {
            $queryParts = $this->resolveQueryParts($filter['operator'], $filter['value']);

            $relations = explode('.', $filter['column']);
            $column = array_pop($relations);

            $func = $this->getFunction($filter);

            if (count($relations) == 0) {
                $this->query($column, $queryParts['operator'], $queryParts['value'], $func);
            } else {
                $this->queryRelated(
                    Str::camel(implode('.', $relations)),
                    $column,
                    $queryParts['operator'],
                    $queryParts['value'],
                    $func,
                );
            }
        }
    }

    protected function query($column, $operator, $value, $func)
    {
        if ($this->model->translatable && in_array($column, $this->model->translatable)) {
            $column = $column . '->' . app()->getLocale();
        }

        switch ($operator) {
            case 'in':
                $this->builder = $this->builder->whereIn($column, $value);
                break;
            case 'is_null':
                $this->builder = $this->builder->whereNull($column);
                break;
            default:
                $this->builder = $this->builder->{$func}($column, $operator, $value);
                break;
        }
    }

    protected function queryRelated($relation, $column, $operator, $value, $func)
    {
        $temp = $func == 'orWhere' ? 'orWhereHas' : 'whereHas';
        $this->builder = $this->builder->{$temp}($relation, function ($query) use ($column, $operator, $value) {
            $relatedTableName = $query->getModel()->getTable();

            if ($query->getModel()->translatable && in_array($column, $query->getModel()->translatable)) {
                $column = $column . '->' . app()->getLocale();
            }

            switch ($operator) {
                case 'in':
                    return $query->whereIn($relatedTableName . '.' . $column, $value);
                case 'is_null':
                    return $query->whereNull($relatedTableName . '.' . $column);
                default:
                    return $query->where($relatedTableName . '.' . $column, $operator, $value);
            }
        });
    }

    protected function getFunction($filter)
    {
        $functions = ['where', 'orWhere', 'whereNull'];
        if (! array_key_exists('function', $filter)) {
            return 'where';
        }

        if (! in_array($filter['function'], $functions)) {
            return 'where';
        }

        if ($filter['operator'] == 'is_null') {
            return 'whereNull';
        }

        return $filter['function'];
    }

    protected function resolveQueryParts($operator, $value)
    {
        return Arr::get([
            'not_equals' => [
                'operator' => '!=',
                'value' => $value,
            ],
            'equals' => [
                'operator' => '=',
                'value' => $value,
            ],
            'contains' => [
                'operator' => 'LIKE',
                'value' => "%{$value}%",
            ],
            'starts_with' => [
                'operator' => 'LIKE',
                'value' => "{$value}%",
            ],
            'ends_with' => [
                'operator' => 'LIKE',
                'value' => "%{$value}",
            ],
            'greater_than' => [
                'operator' => '>',
                'value' => $value,
            ],
            'less_than' => [
                'operator' => '<',
                'value' => $value,
            ],
            'greater_than_or_equal_to' => [
                'operator' => '>=',
                'value' => $value,
            ],
            'less_than_or_equal_to' => [
                'operator' => '<=',
                'value' => $value,
            ],
            'is_null' => [
                'operator' => 'is_null',
                'value' => '',
            ],
            'in' => [
                'operator' => 'in',
                'value' => explode(',', $value),
            ],
        ], $operator);
    }
}
