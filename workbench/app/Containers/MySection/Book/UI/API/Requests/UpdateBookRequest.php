<?php

namespace Workbench\App\Containers\MySection\Book\UI\API\Requests;

use Illuminate\Validation\Rule;
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
        'ids.*',
        'authors.*.id',
        'nested.id',
        'nested.ids.*',
        'optional_id',
    ];

    public function rules(): array
    {
        $hashIdEnabled = config('apiato.hash-id');

        return [
            'title' => 'required',
            'author_id' => 'required',
            'ids.*' => ['required', Rule::when($hashIdEnabled, 'integer', 'string')],
            'authors.*.id' => ['required', Rule::when($hashIdEnabled, 'integer', 'string')],
            'nested.id' => ['required', Rule::when($hashIdEnabled, 'integer', 'string')],
            'nested.ids' => 'array',
            'nested.ids.*' => Rule::when($hashIdEnabled, 'integer', 'string'),
        ];
    }

    public function authorize(): bool
    {
        return $this->check([
            'hasAccess',
        ]);
    }
}
