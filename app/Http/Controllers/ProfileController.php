<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $employee = $request->user();
        $view = $employee->isAdmin() ? 'admin.profile.edit' : 'karyawan.profile.edit';

        return view($view, compact('employee'));
    }

    public function update(Request $request): RedirectResponse
    {
        $employee = $request->user();
        $requiresPasswordChange = $employee->must_change_password;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email,'.$employee->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:laki_laki,perempuan'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', File::image()->max(2048)],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($requiresPasswordChange && empty($validated['password'])) {
            return back()
                ->withErrors(['password' => 'Anda wajib mengganti password bawaan sebelum melanjutkan.'])
                ->withInput();
        }

        if (! empty($validated['password'])) {
            if (empty($validated['current_password']) || ! Hash::check($validated['current_password'], $employee->password)) {
                return back()
                    ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                    ->withInput();
            }
        }

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }

            $validated['photo_path'] = $request->file('photo')->store('profiles', 'public');
        }

        $employee->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'place_of_birth' => $validated['place_of_birth'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'photo_path' => $validated['photo_path'] ?? $employee->photo_path,
        ]);

        if (! empty($validated['password'])) {
            $employee->password = $validated['password'];
            $employee->must_change_password = false;
        }

        $employee->save();

        session()->flash('success', $requiresPasswordChange && ! empty($validated['password'])
            ? 'Password berhasil diperbarui. Silakan lanjut menggunakan sistem.'
            : 'Profil berhasil diperbarui.');

        if ($requiresPasswordChange && ! empty($validated['password'])) {
            $route = $employee->isAdmin()
                ? 'admin.dashboard'
                : 'karyawan.dashboard';

            return redirect()->route($route);
        }

        return back();
    }
}
