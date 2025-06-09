<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\Book\UI\API\Requests;

use Workbench\App\Ship\Parents\Requests\Request as ParentRequest;

class CreateBookRequest extends ParentRequest
{
    protected array $decode = [
        // 'id',
    ];

    public function rules(): array
    {
        return [
            // 'id' => 'required',
        ];
    }
}
