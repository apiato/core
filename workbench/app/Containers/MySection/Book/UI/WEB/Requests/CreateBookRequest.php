<?php

namespace Workbench\App\Containers\MySection\Book\UI\WEB\Requests;

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
