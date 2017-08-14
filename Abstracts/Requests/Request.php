<?php

namespace Apiato\Core\Abstracts\Requests;

use Apiato\Core\Traits\StateKeeperTrait;
use App;
use App\Containers\Authentication\Tasks\GetAuthenticatedUserTask;
use App\Containers\User\Models\User;
use Apiato\Core\Traits\HashIdTrait;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;
use Illuminate\Support\Facades\Config;

/**
 * Class Request
 *
 * A.K.A (app/Http/Requests/Request.php)
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
abstract class Request extends LaravelFormRequest
{

    use HashIdTrait;
    use StateKeeperTrait;

    /**
     * check if a user has permission to perform an action.
     * User can set multiple permissions (separated with "|") and if the user has
     * any of the permissions, he will be authorize to proceed with this action.
     *
     * @param \App\Containers\User\Models\User|null $user
     *
     * @return  bool
     */
    public function hasAccess(User $user = null)
    {
        // if not in parameters, take from the request object {$this}
        $user = $user ? : $this->user();

        if($user) {
            $autoAccessRoles = Config::get('apiato.requests.allow-roles-to-access-all-routes');
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
            $this->hasAnyRoleAccess($user)
        );

        // allow access if user has access to any of the defined roles or permissions.
        return empty($hasAccess) ? true : in_array(true, $hasAccess);
    }

    /**
     * Check if the submitted ID (mainly URL ID's) is the same as
     * the authenticated user ID (based on the user Token).
     *
     * @return  bool
     */
    public function isOwner()
    {
        return App::make(GetAuthenticatedUserTask::class)->run()->id == $this->id;
    }

    /**
     * To be used mainly from unit tests.
     *
     * @param array                                 $parameters
     * @param \App\Containers\User\Models\User|null $user
     * @param array                                 $cookies
     * @param array                                 $files
     * @param array                                 $server
     *
     * @return  static
     */
    public static function injectData($parameters = [], User $user = null, $cookies = [], $files = [], $server = [])
    {
        // if user is passed, will be returned when asking for the authenticated user using `\Auth::user()`
        if ($user) {
            $app = App::getInstance();
            $app['auth']->guard($driver = 'api')->setUser($user);
            $app['auth']->shouldUse($driver);
        }

        // For now doesn't matter which URI or Method is used.
        return parent::create('/', 'GET', $parameters, $cookies, $files, $server);
    }

    /**
     * Sanitizes the data of a request. This is a superior version of php built-in array_filter() as it preserves
     * FALSE and NULL values as well.
     *
     * @param array $fields a list of fields to be checked in the Dot-Notation (e.g., ['data.name', 'data.description'])
     * @return array an array containing the values if the field was present in the request and the intersection array
     */
    public function sanitizeInput(array $fields)
    {
        // get all request data
        $data = $this->all();

        $search = [];
        foreach($fields as $field) {
            // create a multidimensional array based on $fields
            // which was submitted as DOT notation (e.g., data.name)
            array_set($search, $field, true);
        }

        // check, if the keys exist in both arrays
        $data = $this->recursive_array_intersect_key(
            $data,
            $search
        );

        return $data;
    }

    /**
     * Recursively intersects 2 arrays based on their keys.
     *
     * @param array $a first array (that keeps the values)
     * @param array $b second array to be compared with
     * @return array an array containing all keys that are present in $a and $b. Only values from $a are returned
     */
    private function recursive_array_intersect_key(array $a, array $b) {
        $a = array_intersect_key($a, $b);
        foreach ($a as $key => &$value) {
            if (is_array($value) && is_array($b[$key])) {
                $value = $this->recursive_array_intersect_key($value, $b[$key]);
            }
        }
        return $a;
    }

    /**
     * Overriding this function to modify the any user input before
     * applying the validation rules.
     *
     * @return  array
     */
    public function all()
    {
        $requestData = parent::all();

        $requestData = $this->mergeUrlParametersWithRequestData($requestData);

        $requestData = $this->decodeHashedIdsBeforeValidation($requestData);

        return $requestData;
    }

    /**
     * Used from the `authorize` function if the Request class.
     * To call functions and compare their bool responses to determine
     * if the user can proceed with the request or not.
     *
     * @param array $functions
     *
     * @return  bool
     */
    protected function check(array $functions)
    {
        $orIndicator = '|';
        $returns = [];

        // iterate all functions in the array
        foreach ($functions as $function) {

            // in case the value doesn't contains a separator (single function per key)
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
                $returns[] = in_array(true, $orReturns) ? true : false;
            }
        }

        // if in_array returned `true` means a function returned `false` thus return `false` to prevent access.
        // if in_array returned `false` means all functions returned `true` thus return `true` to allow access.
        // return the final boolean
        return in_array(false, $returns) ? false : true;
    }

    /**
     * apply validation rules to the ID's in the URL, since Laravel
     * doesn't validate them by default!
     *
     * Now you can use validation riles like this: `'id' => 'required|integer|exists:items,id'`
     *
     * @param array $requestData
     *
     * @return  array
     */
    private function mergeUrlParametersWithRequestData(Array $requestData)
    {
        if (isset($this->urlParameters) && !empty($this->urlParameters)) {
            foreach ($this->urlParameters as $param) {
                $requestData[$param] = $this->route($param);
            }
        }

        return $requestData;
    }

    /**
     * @param $user
     *
     * @return  array
     */
    private function hasAnyPermissionAccess($user)
    {
        if (!array_key_exists('permissions', $this->access) || !$this->access['permissions']) {
            return [];
        }

        $permissions = explode('|', $this->access['permissions']);

        $hasAccess = array_map(function ($permission) use ($user) {
            // Note: internal return
            return $user->hasPermissionTo($permission);
        }, $permissions);

        return $hasAccess;
    }

    /**
     * @param $user
     *
     * @return  array
     */
    private function hasAnyRoleAccess($user)
    {
        if (!array_key_exists('roles', $this->access) || !$this->access['roles']) {
            return [];
        }

        $roles = explode('|', $this->access['roles']);

        $hasAccess = array_map(function ($role) use ($user) {
            // Note: internal return
            return $user->hasRole($role);
        }, $roles);

        return $hasAccess;
    }

    /**
     * This method mimics the $request->input() method but works on the "decoded" values
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    public function getInputByKey($key = null, $default = null)
    {
        return data_get($this->all(), $key, $default);
    }

}
