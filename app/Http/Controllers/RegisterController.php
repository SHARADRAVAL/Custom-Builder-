<?php

namespace App\Http\Controllers;

use App\Models\Register;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('register');
    }

    // register functions
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $register = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if (!$register) {
            return back()->with('error', 'Registration failed! Please try again.');
        } else {

            return redirect()->route('login.form')->with('success', 'Registration successful! Please login.');
        }
    }

    // login functions
    public function ShowLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (!Auth::attempt($credentials)) {
            return back()->with('error', 'Login failed! Please check your credentials and try again.');
        }
        // $request->session()->regenerate();

        return view('dashboard')->with('success', 'Login successful!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // $request->session()->invalidate();

        // $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');
    }

    public function datatable()
    {
        return DataTables::of(Forms::query())
            ->addColumn('action', function ($form) {
                return '
                <div class="dropdown text-end">
                    <button class="btn btn-sm btn-light p-0 border-0" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots text-primary fs-5"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item text-primary"
                               href="' . route('forms.edit', $form->id) . '">
                               <i class="bi bi-pencil me-2"></i>Edit
                            </a>
                        </li>

                        <li>
                            <form method="POST" action="' . route('forms.destroy', $form->id) . '">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button class="dropdown-item text-danger">
                                    <i class="bi bi-trash me-2"></i>Delete
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
