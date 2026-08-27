<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $this->validateCredentials($request);
        $user = $this->authenticatedUser($credentials);

        if (! $user) {
            return $this->invalidCredentials();
        }

        $isScannerAccount = $user->employee?->department?->office === 'GSD'
            || $user->role === Role::SUPERADMIN
            || in_array($user->email, ['dapsec@dap.edu.ph', 'dapcc-sec@dap.edu.ph'], true);

        if (! $isScannerAccount) {
            activity('mobile login denied')->causedBy($user)->log('A user attempted to access a restricted mobile application.');

            return response()->json(['message' => 'This account is not authorized for the mobile application.'], 403);
        }

        $token = $user->createToken($user->hris_number.'-access', ['attendance:read', 'attendance:write'])->plainTextToken;
        $latest = TimeEntry::where('hris_number', $user->hris_number)
            ->whereDate('time_start', now()->toDateString())
            ->latest('time_start')
            ->first();

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'start' => $latest !== null && $latest->time_end === null,
        ]);
    }

    public function mvpool_login(Request $request): JsonResponse
    {
        $credentials = $this->validateCredentials($request);
        $user = $this->authenticatedUser($credentials);

        if (! $user) {
            return $this->invalidCredentials();
        }

        $tokenName = $user->hris_number.'-mvpool-access';
        $response = $user->tokens()->where('name', $tokenName)->exists()
            ? 'token-existed'
            : $user->createToken($tokenName, ['mvpool:read', 'mvpool:write'])->plainTextToken;

        return response()->json(['status' => 'success', 'response' => $response]);
    }

    public function v1_mvpool_login(Request $request): JsonResponse
    {
        $credentials = $this->validateCredentials($request);
        $validated = $request->validate([
            'token' => ['nullable', 'string', 'max:255'],
        ]);
        $user = $this->authenticatedUser($credentials);

        if (! $user) {
            return $this->invalidCredentials();
        }

        $response = ($validated['token'] ?? null) === 'not-exists'
            ? $user->createToken($user->hris_number.'-mvpool-access', ['mvpool:read', 'mvpool:write'])->plainTextToken
            : 'token-exists';

        return response()->json(['response' => $response]);
    }

    /**
     * @return array{email: string, password: string}
     */
    private function validateCredentials(Request $request): array
    {
        return $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * @param  array{email: string, password: string}  $credentials
     */
    private function authenticatedUser(array $credentials): ?User
    {
        $user = User::with('employee.department')->where('email', $credentials['email'])->first();

        return $user && Hash::check($credentials['password'], $user->password) ? $user : null;
    }

    private function invalidCredentials(): JsonResponse
    {
        return response()->json(['message' => 'Invalid email or password.'], 401);
    }
}
