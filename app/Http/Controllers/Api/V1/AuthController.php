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
                if ($time_entry) {
                    $start = true;
                    if ($time_entry->time_end) {
                        $start = false;
                    } else {
                        $start = true;
                    }
                } else {
                    $start = false;
                }

                return response()->json(['status' => 'success', 'token' => $token, 'start' => $start]);
            } else {
                if ($user->email == 'dapsec@dap.edu.ph' || $user->email == 'dapcc-sec@dap.edu.ph') {
                    $token = $user->createToken($user->id)->plainTextToken;
                    $endpoint = env('AZURE_STORAGE_API_ENDPOINT');
                    $sas_token = env('AZURE_STORAGE_SAS_TOKEN');

                    return response()->json(['token' => $token, 'endpoint' => $endpoint, 'sas_token' => $sas_token], 200);
                } else {
                    activity('user logged in in apks')->log($user->employee->employee->full_name . ' tried to login in DAP official mobile applications');
                    return response()->json(['message' => 'You do not have any right to login into this application! This activity will be logged.'], 422);
                }
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

    public function v1_mvpool_login(Request $request)
    {
        if ($request->email == null || $request->password == null) {
            return response()->json(['response' => 'The email/password field is required!'], 400);
        }

        if (Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = User::where('email', $request->email)->first();
            // info($user);
            // $response = $user->createToken($user->hris_number . '-access')->plainTextToken;

            if ($request->token == "not-exists") {
                $response = $user->createToken($user->hris_number . '-access')->plainTextToken;
            } else {
                $response = "token-exists";
            }
            // if (count($user->tokens->where('name', $user->hris_number . '-access')) > 0) {
            //     $response = "token-existed";
            // } else {
            // }
            // $token = $user->createToken($user->hris_number.'-access', ['mvpool:access'])->plainTextToken;

            return response()->json(['response' => $response], 200);

            // if ($user->employee->department->office == 'GSD' || $user->role == Role::SUPERADMIN) {
            //     if ($request->token == "not-exists") {
            //         $token = $user->createToken($user->hris_number . '-access')->plainTextToken;
            //     } else {
            //         $token = "token-exists";
            //     }
            //     $time_entry = TimeEntry::where('hris_number', $user->hris_number)->whereDate('time_start', now()->format('Y-m-d'))->orderBy('time_start', 'desc')->first();
            //     if ($time_entry) {
            //         $start = true;
            //         if ($time_entry->time_end) {
            //             $start = false;
            //         } else {
            //             $start = true;
            //         }
            //     } else {
            //         $start = false;
            //     }

            //     return response()->json(['status' => 'success', 'token' => $token, 'start' => $start], 200);
            // } else {
            //     activity('user logged in in apks')->log($user->employee->full_name . ' tried to sign in into DAP official mobile applications');
            //     return response()->json(['response' => 'You do not have any right to login into this application! This activity will be logged.'], 400);
            // }
        } else {
            return response()->json(['response' => 'Incorrect Credentials provided!'], 400);
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
