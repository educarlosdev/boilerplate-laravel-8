<?php

namespace App\Helper;

use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class Util
{
    public $version;

    public function __construct()
    {
        $this->version = '1.0.0';
    }

    public function queueDriver(): string
    {
        return getenv('APP_ENV') == "production"
            ? 'redis'
            : 'sync';
    }

    public static function paginate(Collection $results, $pageSize)
    {
        $page = Paginator::resolveCurrentPage('page');

        $total = $results->count();

        $paginator = self::paginator($results->forPage($page, $pageSize), $total, $pageSize, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
            'query' => request()->all(),
        ]);

        $updateItems = collect(collect($paginator->getCollection()->toArray())->values()->all());

        $paginator->setCollection($updateItems);

        return $paginator;

    }

    protected static function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }
}
