<?php

namespace App\Application\Port;

use App\Domain\Products\DTO\CreateProductDto;

interface ProductCreatePort
{
    /**
     * @return array{mode:string, id:int|null, status:int, message?:string, payload?:array}
     *   - mode: "sync"|"async"
     *   - id: id do produto quando sync; null quando async
     *   - status: HTTP sugerido (201/202/409/400)
     *   - message/payload: informações opcionais
     */
    public function create(CreateProductDto $dto, bool $forceAsync = null): array;
}
