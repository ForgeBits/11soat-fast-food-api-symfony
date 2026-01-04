<?php

namespace App\Application\Domain\Dtos\Commons;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class PaginationQueryDto
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public int $page = 1;

    #[Assert\NotNull]
    #[Assert\Positive]
    public int $perPage = 10;

    #[Assert\NotNull]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(100)]
    public int $limit = 10;

    #[Assert\NotNull]
    public string $orderBy = 'id';

    #[Assert\Choice(choices: ['ASC', 'DESC'])]
    public string $orderDirection = 'ASC';

    public int $offset = 0;

    public static function fromRequest(Request $request): self
    {
        $dto = new self();

        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 10);
        $limit = (int) $request->query->get('limit', 10);
        $orderBy = $request->query->get('orderBy', 'id');
        $orderDirection = strtoupper($request->query->get('orderDirection', 'ASC'));

        $dto->page = max(1, $page);
        $dto->perPage = min(max($perPage, 1), 100);
        $dto->limit = min(max($limit, 1), 100);
        $dto->offset = ($dto->page - 1) * $dto->limit;
        $dto->orderBy = $orderBy;
        $dto->orderDirection = in_array($orderDirection, ['ASC', 'DESC']) ? $orderDirection : 'ASC';

        return $dto;
    }
}
