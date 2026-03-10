<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        Log::info('Login attempt started', [
            'input' => $request->only('gsis_id')
        ]);

        $credentials = $request->validate([
            'gsis_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $authenticatedUser = Auth::user();

            Log::info('Login successful', [
                'user_id' => $authenticatedUser->id,
                'gsis_id' => $authenticatedUser->gsis_id,
            ]);

            if (!$authenticatedUser->employee) {
                return back()->withErrors([
                    'gsis_id' => 'Employee data not found.',
                ]);
            }

            $deptCode = $authenticatedUser->employee->department->department_code;
            $route = $this->getDepartmentRoute($deptCode);

            Log::info('Redirecting', [
                'route' => $route,
                'full_url' => url($route),
            ]);

            return Inertia::location($route); // Pass relative path, not full URL
        }

        Log::warning('Login failed', [
            'gsis_id' => $request->gsis_id,
        ]);

        return back()->withErrors([
            'gsis_id' => 'The provided GSIS ID or password is incorrect.',
        ])->onlyInput('gsis_id');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8', 'confirmed']
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        $user->password = $request->new_password;
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(route('login'));
    }

    private function getDepartmentRoute($code)
    {
        $mapping = [
            'LM001' => '/hr-dashboard',       // Just the path
            'LM005' => '/build/dashboard',
            'LM006' => '/build/dashboard',
            'LM007' => '/build/dashboard',
        ];

        return $mapping[$code] ?? '/';
    }

    public function createToken(Request $request)
    {
        $credentials = $request->validate([
            'gsis_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
