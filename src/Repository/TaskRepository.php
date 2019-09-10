<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class TaskRepository
 * @package App\Repository
 */
class TaskRepository extends EntityRepository
{
    const TABLE_ALIAS = 't';

    private $sortingMap = [
        'id' => self::TABLE_ALIAS . '.id',
        'username' => self::TABLE_ALIAS . '.username',
        'email' => self::TABLE_ALIAS . '.email',
        'is_completed' => self::TABLE_ALIAS . '.isCompleted'
    ];

    /**
     * @param int $page
     * @param int $itemsPerPage
     * @param string $sortBy
     * @param string $sortDirection
     * @return Paginator
     */
    public function getPaginator(int $page, int $itemsPerPage, string $sortBy, string $sortDirection): Paginator
    {
        $queryBuilder = $this->createQueryBuilder(self::TABLE_ALIAS);

        if (isset($this->sortingMap[$sortBy])) {
            $queryBuilder->orderBy($this->sortingMap[$sortBy], $sortDirection);
        }

        $firstItemIndex = $page * $itemsPerPage - $itemsPerPage;

        $queryBuilder
            ->setFirstResult($firstItemIndex)
            ->setMaxResults($itemsPerPage)
        ;

        $paginator = new Paginator($queryBuilder, true);

        return $paginator;
    }
}
