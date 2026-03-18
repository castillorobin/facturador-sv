<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\Company;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // 1. Creamos una empresa básica para este nuevo suscriptor
    $company = Company::create([
        'nombre' => 'Mi Empresa - ' . $request->name,
        'nombre_comercial' => 'Nombre Comercial',
        'nit' => '00000000000000', // Datos temporales
        'nrc' => '000000',
        'cod_actividad' => '00000',
        'desc_actividad' => 'Actividad económica',
        'departamento' => '01',
        'municipio' => '01',
        'direccion_complemento' => 'Dirección pendiente',
        'telefono' => '00000000',
        'email' => $request->email,
        'api_usuario' => '',
        'api_password' => '',
        'password_privado' => '',
    ]);

    // 2. Creamos el usuario vinculado a esa empresa
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'company_id' => $company->id, // <--- Aquí le pasamos el ID recién creado
    ]);

    event(new Registered($user));
    Auth::login($user);

    return redirect(route('dashboard', absolute: false));
}
}
