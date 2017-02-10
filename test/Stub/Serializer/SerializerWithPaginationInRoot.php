<?php

namespace League\Fractal\Test\Stub\Serializer;

use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Serializer\DataArraySerializer;

final class SerializerWithPaginationInRoot extends DataArraySerializer
{
    /**
     * {@inheritdoc}
     */
    public function meta(array $meta): array
    {
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function paginator(PaginatorInterface $paginator)
    {
        return [
            'total' => (int) $paginator->getTotal(),
            'count' => (int) $paginator->getCount(),
            'per_page' => (int) $paginator->getPerPage(),
            'current_page' => (int) $paginator->getCurrentPage(),
            'total_pages' => (int) $paginator->getLastPage(),
        ];
    }
}
