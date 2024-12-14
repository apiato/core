<?php

namespace Apiato\Core\Abstracts\Requests;

use Apiato\Core\Abstracts\Models\UserModel as User;
use Apiato\Core\Exceptions\IncorrectIdException;
use Apiato\Core\Traits\HashIdTrait;
use Apiato\Core\Traits\SanitizerTrait;
use Illuminate\Foundation\Http\FormRequest as LaravelRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

abstract class Request extends LaravelRequest
{
    use HashIdTrait;
    use SanitizerTrait;

    /**
     * Roles and/or Permissions that has access to this request.
     *
     * @example ['permissions' => 'create-users', 'roles' => 'admin|manager']
     * @example ['permissions' => null, 'roles' => 'admin']
     * @example ['permissions' => ['create-users'], 'roles' => null]
     *
     * @var array<string, string|array<string>|null>
     */
    protected array $access = [
        'permissions' => null,
        'roles' => null,
    ];

    /**
     * Id's that needs decoding before applying the validation rules.
     *
     * @example ['id']
     *
     * @var string[]
     */
    protected array $decode = [];

    /**
     * Defining the URL parameters (`/stores/{slug}/items`) allows applying
     * validation rules on them and allows accessing them like request data.
     *
     * For example, you can use the `exists` validation rule on the `slug` parameter.
     * And you can access the `slug` parameter using `$request->slug`.
     *
     * @example ['slug']
     *
     * @var string[]
     */
    protected array $urlParameters = [];

    /**
     * To be used mainly from unit tests.
     */
    public static function injectData(array $parameters = [], User|null $user = null, array $cookies = [], array $files = [], array $server = []): static
    {
        // if user is passed, will be returned when asking for the authenticated user using `\Auth::user()`
        if ($user) {
            $app = App::getInstance();
            $app['auth']->guard($driver = 'api')->setUser($user);
            $app['auth']->shouldUse($driver);
        }

        // For now doesn't matter which URI or Method is used.
        $request = parent::create('/', 'GET', $parameters, $cookies, $files, $server);

        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $request;
    }

    /**
     * Add properties to the request that are not part of the request body
     * but are needed for the request to be processed.
     * For example, in the unit tests, we can add the url parameters to the request which is not part of the request body.
     * It is best used with the `injectData` method.
     *
     * @return $this
     */
    public function withUrlParameters(array $properties): self
    {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    public function getAccessArray(): array
    {
        return $this->access;
    }

    public function getDecodeArray(): array
    {
        return $this->decode;
    }

    public function getUrlParametersArray(): array
    {
        return $this->urlParameters;
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
        return empty($hasAccess) || in_array(true, $hasAccess, true);
    }

    protected function hasAnyPermissionAccess($user): array
    {
        if (!array_key_exists('permissions', $this->access) || !$this->access['permissions']) {
            return [];
        }

        $permissions = is_array($this->access['permissions']) ? $this->access['permissions'] :
            explode('|', $this->access['permissions']);

        return array_map(static function ($permission) use ($user) {
            return $user->hasPermissionTo($permission);
        }, $permissions);
    }

    protected function hasAnyRoleAccess($user): array
    {
        if (!array_key_exists('roles', $this->access) || !$this->access['roles']) {
            return [];
        }

        $roles = is_array($this->access['roles']) ? $this->access['roles'] :
            explode('|', $this->access['roles']);

        return array_map(static function ($role) use ($user) {
            return $user->hasRole($role);
        }, $roles);
    }

    /**
     * Maps Keys in the Request.
     *
     * For example, ['data.attributes.name' => 'firstname'] would map the field [data][attributes][name] to [firstname].
     * Note that the old value (data.attributes.name) is removed the original request - this method manipulates the request!
     * Be sure you know what you do!
     *
     * @throws IncorrectIdException
     * @throws \Throwable
     */
    public function mapInput(array $fields): void
    {
        $data = $this->all();

        foreach ($fields as $oldKey => $newKey) {
            // the key to be mapped does not exist - skip it
            if (!Arr::has($data, $oldKey)) {
                continue;
            }

            // set the new field and remove the old one
            Arr::set($data, $newKey, Arr::get($data, $oldKey));
            Arr::forget($data, $oldKey);
        }

        // overwrite the initial request
        $this->replace($data);
    }

    /**
     * Overriding this function to modify the any user input before
     * applying the validation rules.
     *
     * @param null $keys
     *
     * @throws IncorrectIdException
     * @throws \Throwable
     */
    public function all($keys = null): array
    {
        $requestData = parent::all($keys);

        $requestData = $this->mergeUrlParametersWithRequestData($requestData);

        $requestData = $this->decodeHashedIdsBeforeValidation($requestData);

        return $requestData;
    }

    /**
     * apply validation rules to the ID's in the URL, since Laravel
     * doesn't validate them by default!
     *
     * Now you can use validation rules like this: `'id' => 'required|integer|exists:items,id'`
     */
    protected function mergeUrlParametersWithRequestData(array $requestData): array
    {
        if (!empty($this->urlParameters)) {
            foreach ($this->urlParameters as $param) {
                $requestData[$param] = $this->route($param);
            }
        }

        return $requestData;
    }

    /**
     * This method mimics the $request->input() method but works on the "decoded" values.
     *
     * @throws IncorrectIdException
     * @throws \Throwable
     */
    public function getInputByKey($key = null, $default = null): mixed
    {
        return data_get($this->all(), $key, $default);
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
            if (!strpos($function, $orIndicator)) {
                // simply call the single function and store the response.
                $returns[] = $this->{$function}();
            } else {
                // in case the value contains a separator (multiple functions per key)
                $orReturns = [];

                // iterate over each function in the key
                foreach (explode($orIndicator, $function) as $orFunction) {
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
