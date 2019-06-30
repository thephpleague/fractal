<?php

use League\Fractal\Manager;
use League\Fractal\Pagination\SimplePaginationAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;

require '../vendor/autoload.php';

$page = $_REQUEST['page'] ?? 1;
$limit = $_REQUEST['limit'] ?? 10;
$start = min(1000, ($limit * ($page - 1)) + 1);
$end = min(1000, $start + $limit) - 1;
$data = range($start, $end);

$fractal = new Manager();
$collection = new Collection($data);
$paginator = new SimplePaginationAdapter($page, count($data), $limit, 1000, function (int $page) use ($limit) {
    return '?' . http_build_query(['limit' => $limit, 'page' => $page]);
});
$collection->setPaginator($paginator);

echo '<pre>' . $fractal->createData($collection)->toJson(JSON_PRETTY_PRINT);
