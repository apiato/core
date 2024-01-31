<?php

namespace Apiato\Core\Traits\TestTraits\PhpUnit;

use Apiato\Core\Abstracts\Models\UserModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

trait TestAuthHelperTrait
{
    /**
     * Logged in user object.
     */
    protected UserModel|null $testingUser = null;

    /**
     * User class used by factory to create testing user.
     */
    protected string|null $userClass = null;

    /**
     * Roles and permissions, to be attached on the user.
     */
    protected array $access = [
        'permissions' => null,
        'roles' => null,
    ];

    /**
     * state name on User factory.
     */
    private string|null $userAdminState = null;

    /**
     * create testing user as Admin.
     */
    private bool|null $createUserAsAdmin = null;

    /**
     * Same as `getTestingUser()` but always overrides the User Access
     * (roles and permissions) with null. So the user can be used to test
     * if unauthorized user tried to access your protected endpoint.
     *
     * @param null $userDetails
     */
    public function getTestingUserWithoutAccess($userDetails = null, bool $createUserAsAdmin = false): UserModel
    {
        return $this->getTestingUser($userDetails, $this->getNullAccess(), $createUserAsAdmin);
    }

    /**
     * Try to get the last logged-in User, if not found then create new one.
     * Note: if $userDetails are provided it will always create new user, even
     * if another one was previously created during the execution of your test.
     *
     * By default, Users will be given the Roles and Permissions found in the class
     * `$access` property. But the $access parameter can be used to override the
     * defined roles and permissions in the `$access` property of your class.
     *
     * @param array|null $userDetails what to be attached on the User object
     * @param array|null $access roles and permissions you'd like to provide this user with
     * @param bool $createUserAsAdmin should create testing user as admin
     */
    public function getTestingUser(array|null $userDetails = null, array|null $access = null, bool $createUserAsAdmin = false): UserModel
    {
        $this->createUserAsAdmin = $createUserAsAdmin;
        $this->userClass = $this->userclass ?? Config::get('apiato.tests.user-class');
        $this->userAdminState = Config::get('apiato.tests.user-admin-state');

        if (is_null($userDetails)) {
            return $this->findOrCreateTestingUser($userDetails, $access);
        }

        return $this->createTestingUser($userDetails, $access);
    }

    private function findOrCreateTestingUser($userDetails, $access): UserModel
    {
        return $this->testingUser ?: $this->createTestingUser($userDetails, $access);
    }

    private function createTestingUser(array|null $userDetails = null, array|null $access = null): UserModel
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

    private function factoryCreateUser(array|null $userDetails = null): UserModel
    {
        $user = str_replace('::class', '', $this->userClass);
        if ($this->createUserAsAdmin) {
            $state = $this->userAdminState;

            return $user::factory()->$state()->create($this->prepareUserDetails($userDetails));
        }

        return $user::factory()->create($this->prepareUserDetails($userDetails));
    }

    private function prepareUserDetails(array|null $userDetails = null): array
    {
        $defaultUserDetails = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'testing-password',
        ];

        // if no user detail provided, use the default details, to find the password or generate one before encoding it
        return $this->prepareUserPassword($userDetails ?: $defaultUserDetails);
    }

    private function prepareUserPassword(array|null $userDetails): array|null
    {
        // get password from the user details or generate one
        $password = $userDetails['password'] ?? $this->faker->password;

        // hash the password and set it back at the user details
        $userDetails['password'] = Hash::make($password);

        return $userDetails;
    }

    private function setupTestingUserAccess($user, array|null $access = null)
    {
        $access = $access ?: $this->getAccess();

        $user = $this->setupTestingUserPermissions($user, $access);
        $user = $this->setupTestingUserRoles($user, $access);

        return $user;
    }

    private function getAccess(): array|null
    {
        return $this->access ?? null;
    }

    private function setupTestingUserPermissions($user, array|null $access)
    {
        if (!empty($access['permissions'])) {
            $user->givePermissionTo($access['permissions']);
            $user = $user->fresh();
        }

        return $user;
    }

    private function setupTestingUserRoles($user, array|null $access)
    {
        if (!empty($access['roles']) && !$user->hasRole($access['roles'])) {
            $user->assignRole($access['roles']);
            $user = $user->fresh();
        }

        return $user;
    }

    private function getNullAccess(): array
    {
        return [
            'permissions' => null,
            'roles' => null,
        ];
    }
}
