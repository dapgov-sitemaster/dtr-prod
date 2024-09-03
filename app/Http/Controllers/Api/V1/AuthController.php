<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $attempt = Auth::guard('web')->attempt($validated);

        if ($attempt) {
            $user = User::with('employee')->where('email', $request->email)->first();

            if ($user->employee->department->office == 'GSD' || $user->role == Role::SUPERADMIN) {
                $token = $user->createToken($user->hris_number . '-access')->plainTextToken;
                $time_entry = TimeEntry::where('hris_number', $user->hris_number)->whereDate('time_start', now()->format('Y-m-d'))->orderBy('time_start', 'desc')->first();
                return response()->json(['status' => 'success', 'token' => $token, 'start' => ($time_entry->time_end == null ? false : true)]);
            } else {
                $token = $user->createToken($user->id)->plainTextToken;
                $endpoint = env('AZURE_STORAGE_API_ENDPOINT');
                $sas_token = env('AZURE_STORAGE_SAS_TOKEN');

                return response()->json(['token' => $token, 'endpoint' => $endpoint, 'sas_token' => $sas_token], 200);
            }
        } else {
            return response()->json(['message' => 'User credentials are not correct.'], 422);
        }
    }

    public function mvpool_login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);
        if (Auth::guard('web')->attempt($fields)) {
            $user = User::where('email', $request->email)->first();

            if (count($user->tokens->where('name', $user->hris_number . '-access')) > 0) {
                $response = "token-existed";
            } else {
                $response = $user->createToken($user->hris_number . '-access')->plainTextToken;
            }
            // $token = $user->createToken($user->hris_number.'-access', ['mvpool:access'])->plainTextToken;

            return response()->json(['status' => 'success', 'response' => $response]);
        } else {
            return response()->json(['status' => 'failed']);
        }
    }

    // public function old_login(Request $request)
    // {
    //     $validated = $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     $attempt = Auth::guard('web')->attempt($validated);

    //     if ($attempt) {
    //         $user = User::where('email', $request->email)->first();
    //         $token = $user->createToken($user->id)->plainTextToken;

    //         $endpoint = env('AZURE_STORAGE_API_ENDPOINT');
    //         $sas_token = env('AZURE_STORAGE_SAS_TOKEN');

    //         return response()->json(['token' => $token, 'endpoint' => $endpoint, 'sas_token' => $sas_token], 200);
    //     } else {
    //         return response()->json(['message' => 'User credentials are not correct.'], 422);
    //     }
    // }
}
