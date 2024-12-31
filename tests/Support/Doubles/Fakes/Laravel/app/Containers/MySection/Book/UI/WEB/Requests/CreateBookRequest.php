<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\WEB\Requests;

use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Requests\Request as ParentRequest;

class CreateBookRequest extends ParentRequest
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
