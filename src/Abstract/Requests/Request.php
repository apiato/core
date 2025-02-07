<?php

namespace Apiato\Abstract\Requests;

use Apiato\Abstract\Models\UserModel as User;
use Illuminate\Foundation\Http\FormRequest as LaravelRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

abstract class Request extends LaravelRequest
{
    /**
     * Roles and/or Permissions that has access to this request.
     *
     * @example ['permissions' => 'create-users', 'roles' => 'admin|manager']
     * @example ['permissions' => null, 'roles' => 'admin']
     * @example ['permissions' => ['create-users'], 'roles' => null]
     *
     * @var array<string, string|null>
     */
    protected array $access = [
        'permissions' => null,
        'roles' => null,
    ];

    /**
     * Id's that needs decoding before applying the validation rules.
     *
     * @example ['id', 'author_ids.*', 'nested.id', 'nested.ids.*', 'nested.*.id']
     *
     * @var string[]
     */
    protected array $decode = [];

    /**
     * To be used mainly from unit tests.
     */
    public static function injectData(array $parameters = [], User|null $user = null, array $cookies = [], array $files = [], array $server = []): static
    {
        // if user is passed, will be returned when asking for the authenticated user using `\Auth::user()`
        if ($user instanceof User) {
            $app = App::getInstance();
            $app['auth']->guard($driver = 'api')->setUser($user);
            $app['auth']->shouldUse($driver);
        }

        // For now doesn't matter which URI or Method is used.
        $request = parent::create('/', 'GET', $parameters, $cookies, $files, $server);

        $request->setUserResolver(static fn (): User|null => $user);

        return $request;
    }

    /**
     * Add properties to the request that are not part of the request body
     * but are needed for the request to be processed.
     * For example, in the unit tests, we can add the url parameters to the request which is not part of the request body.
     * It is best used with the `injectData` method.
     *
     * @param array<string, mixed> $properties
     */
    public function withUrlParameters(array $properties): static
    {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Get the access array.
     *
     * @return array<string, string|null>
     */
    public function getAccessArray(): array
    {
        return $this->access;
    }

    /**
     * Get the decode array.
     *
     * @return string[]
     */
    public function getDecodeArray(): array
    {
        return $this->decode;
    }

    /**
     * check if a user has permission to perform an action.
     * User can set multiple permissions (separated with "|") and if the user has
     * any of the permissions, he will be authorized to proceed with this action.
     */
    public function hasAccess(User|null $user = null): bool
    {
        // if not in parameters, take from the request object {$this}
        $user = $user ?: $this->user();

        if ($user) {
            $autoAccessRoles = config('apiato.requests.allow-roles-to-access-all-routes');
            // there are some roles defined that will automatically grant access
            if (!empty($autoAccessRoles)) {
                $hasAutoAccessByRole = $user->hasAnyRole($autoAccessRoles);
                if ($hasAutoAccessByRole) {
                    return true;
                }
            }
        }

        // check if the user has any role / permission to access the route
        $hasAccess = array_merge(
            $this->hasAnyPermissionAccess($user),
            $this->hasAnyRoleAccess($user),
        );

        // allow access if user has access to any of the defined roles or permissions.
        return [] === $hasAccess || in_array(true, $hasAccess, true);
    }

    protected function hasAnyPermissionAccess($user): array
    {
        if (!array_key_exists('permissions', $this->access) || !$this->access['permissions']) {
            return [];
        }

        $permissions = is_array($this->access['permissions']) ? $this->access['permissions'] :
            explode('|', $this->access['permissions']);

        return array_map(static fn ($permission) => $user->hasPermissionTo($permission), $permissions);
    }

    protected function hasAnyRoleAccess($user): array
    {
        if (!array_key_exists('roles', $this->access) || !$this->access['roles']) {
            return [];
        }

        $roles = is_array($this->access['roles']) ? $this->access['roles'] :
            explode('|', $this->access['roles']);

        return array_map(static fn ($role) => $user->hasRole($role), $roles);
    }

    public function route($param = null, $default = null)
    {
        if (in_array($param, $this->decode, true) && config('apiato.hash-id')) {
            $value = parent::route($param);

            if (is_null($value)) {
                return $default;
            }

            return hashids()->decode($value);
        }

        return parent::route($param, $default);
    }

    public function input($key = null, $default = null)
    {
        if (!config('apiato.hash-id')) {
            return parent::input($key, $default);
        }

        $data = parent::input();

        $flattened = Arr::dot($data);

        foreach ($flattened as $dotKey => $value) {
            foreach ($this->decode as $pattern) {
                if (Str::is($pattern, $dotKey)) {
                    Arr::set($data, $dotKey, hashids()->decode($value));
                    break;
                }
            }
        }

        return data_get($data, $key, $default);
    }

    /**
     * Used from the `authorize` function if the Request class.
     * To call functions and compare their bool responses to determine
     * if the user can proceed with the request or not.
     */
    protected function check(array $functions): bool
    {
        $orIndicator = '|';
        $returns = [];

        // iterate all functions in the array
        foreach ($functions as $function) {
            // in case the value doesn't contain a separator (single function per key)
            if (!strpos((string) $function, $orIndicator)) {
                // simply call the single function and store the response.
                $returns[] = $this->{$function}();
            } else {
                // in case the value contains a separator (multiple functions per key)
                $orReturns = [];

                // iterate over each function in the key
                foreach (explode($orIndicator, (string) $function) as $orFunction) {
                    // dynamically call each function
                    $orReturns[] = $this->{$orFunction}();
                }

                // if in_array returned `true` means at least one function returned `true` thus return `true` to allow access.
                // if in_array returned `false` means no function returned `true` thus return `false` to prevent access.
                // return single boolean for all the functions found inside the same key.
                $returns[] = in_array(true, $orReturns, true);
            }
        }

        // if in_array returned `true` means a function returned `false` thus return `false` to prevent access.
        // if in_array returned `false` means all functions returned `true` thus return `true` to allow access.
        // return the final boolean
        return !in_array(false, $returns, true);
    }
}
