<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Ce compte est désactivé ou n\'existe pas.',
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            ActivityLog::log('login', "Connexion de {$user->name}");

            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'email' => 'Les identifiants sont incorrects.',
        ]);
    }

    public function logout(Request $request)
    {
        $userName = auth()->user()?->name;

        ActivityLog::log('logout', "Déconnexion de {$userName}");

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
