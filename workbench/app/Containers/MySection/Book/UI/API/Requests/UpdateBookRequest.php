<?php

namespace Workbench\App\Containers\MySection\Book\UI\API\Requests;

use Workbench\App\Ship\Parents\Requests\Request as ParentRequest;

class UpdateBookRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => null,
        'roles' => null,
    ];

    protected array $decode = [
        'id',
        'author_id',
        'nested.id',
    ];

    public function rules(): array
    {
        return [
             'id' => 'required',
        ];
    }

    public function authorize(): bool
    {
        return $this->check([
            'hasAccess',
        ]);
    }
}
