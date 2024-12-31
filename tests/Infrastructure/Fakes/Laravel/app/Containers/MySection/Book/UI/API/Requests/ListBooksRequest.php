<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\API\Requests;

use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Requests\Request as ParentRequest;

class ListBooksRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => null,
        'roles' => null,
    ];

    protected array $decode = [
        // 'id',
    ];

    protected array $urlParameters = [
        // 'id',
    ];

    public function rules(): array
    {
        return [
            // 'id' => 'required',
        ];
    }

    public function authorize(): bool
    {
        return $this->check([
            'hasAccess',
        ]);
    }
}
