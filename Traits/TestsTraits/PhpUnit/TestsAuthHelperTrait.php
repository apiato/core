<?php

namespace Apiato\Core\Traits\TestsTraits\PhpUnit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

trait TestsAuthHelperTrait
{
    /**
     * Logged in user object.
     */
    protected $testingUser = null;

    /**
     * User class used by factory to create testing user
     */
    protected ?string $userClass = null;

    /**
     * Roles and permissions, to be attached on the user
     */
    protected array $access = [
        'roles' => '',
        'permissions' => '',
    ];

    /**
     * state name on User factory
     */
    private ?string $userAdminState = null;

    /**
     * create testing user as Admin.
     */
    private ?bool $createUserAsAdmin = null;

    /**
     * Same as `getTestingUser()` but always overrides the User Access
     * (roles and permissions) with null. So the user can be used to test
     * if unauthorized user tried to access your protected endpoint.
     *
     * @param null $userDetails
     * @return mixed
     */
    public function getTestingUserWithoutAccess($userDetails = null)
    {
        return $this->getTestingUser($userDetails, $this->getNullAccess());
    }

    /**
     * Try to get the last logged in User, if not found then create new one.
     * Note: if $userDetails are provided it will always create new user, even
     * if another one was previously created during the execution of your test.
     *
     * By default Users will be given the Roles and Permissions found int he class
     * `$access` property. But the $access parameter can be used to override the
     * defined roles and permissions in the `$access` property of your class.
     *
     * @param array|null $userDetails what to be attached on the User object
     * @param array|null $access roles and permissions you'd like to provide this user with
     * @param bool $createUserAsAdmin should create testing user as admin
     * @return mixed
     */
    public function getTestingUser(?array $userDetails = null, ?array $access = null, bool $createUserAsAdmin = false)
    {
        $this->createUserAsAdmin = $createUserAsAdmin;
        $this->userClass = $this->userclass ?? Config::get('apiato.tests.user-class');
        $this->userAdminState = Config::get('apiato.tests.user-admin-state');
        return is_null($userDetails) ? $this->findOrCreateTestingUser($userDetails, $access)
            : $this->createTestingUser($userDetails, $access);
    }

    private function findOrCreateTestingUser($userDetails, $access)
    {
        return $this->testingUser ?: $this->createTestingUser($userDetails, $access);
    }

    private function createTestingUser(?array $userDetails = null, ?array $access = null)
    {
        // create new user
        $user = $this->factoryCreateUser($userDetails);

        // assign user roles and permissions based on the access property
        $user = $this->setupTestingUserAccess($user, $access);

        // authentication the user
        $this->actingAs($user, 'api');

        // set the created user
        return $this->testingUser = $user;
    }

    private function factoryCreateUser(?array $userDetails = null)
    {
        $user = str_replace('::class', '', $this->userClass);
        if ($this->createUserAsAdmin) {
            $state = $this->userAdminState;
            return $user::factory()->$state()->create($this->prepareUserDetails($userDetails));
        } else {
            return $user::factory()->create($this->prepareUserDetails($userDetails));
        }
    }

    private function prepareUserDetails(?array $userDetails = null): array
    {
        $defaultUserDetails = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'testing-password',
        ];

        // if no user detail provided, use the default details, to find the password or generate one before encoding it
        return $this->prepareUserPassword($userDetails ?: $defaultUserDetails);
    }

    private function prepareUserPassword(?array $userDetails): ?array
    {
        // get password from the user details or generate one
        $password = $userDetails['password'] ?? $this->faker->password;

        // hash the password and set it back at the user details
        $userDetails['password'] = Hash::make($password);

        return $userDetails;
    }

    private function setupTestingUserAccess($user, ?array $access = null)
    {
        $access = $access ?: $this->getAccess();

        $user = $this->setupTestingUserPermissions($user, $access);
        $user = $this->setupTestingUserRoles($user, $access);

        return $user;
    }

    private function getAccess(): ?array
    {
        return $this->access ?? null;
    }

    private function setupTestingUserPermissions($user, ?array $access)
    {
        if (isset($access['permissions']) && !empty($access['permissions'])) {
            $user->givePermissionTo($access['permissions']);
            $user = $user->fresh();
        }

        return $user;
    }

    private function setupTestingUserRoles($user, ?array $access)
    {
        if (isset($access['roles']) && !empty($access['roles']) && !$user->hasRole($access['roles'])) {
            $user->assignRole($access['roles']);
            $user = $user->fresh();
        }

        return $user;
    }

    private function getNullAccess(): array
    {
        return [
            'permissions' => null,
            'roles' => null
        ];
    }
}
