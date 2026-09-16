<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['userDivisions.division', 'userDivisions.role']);

        return view('profile.show', [
            'title' => 'Profil Saya',
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $canChangePassword = (bool) $request->user()->isAdmin && blank($request->user()->jabatan);
        $rules = [
            'phone' => ['nullable', 'string', 'max:25', 'regex:/^\+?[0-9][0-9\s\-()]{7,24}$/'],
            'photo_profile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($canChangePassword) {
            $rules['current_password'] = ['nullable', 'required_with:password', 'current_password'];
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules, [
            'phone.regex' => 'Nomor WhatsApp tidak valid.',
            'photo_profile.image' => 'File foto profil harus berupa gambar.',
            'photo_profile.mimes' => 'Foto profil harus berformat JPG, PNG, atau WebP.',
            'photo_profile.max' => 'Ukuran foto profil maksimal 2 MB.',
            'current_password.required_with' => 'Password saat ini wajib diisi untuk mengganti password.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        $user = $request->user();
        $changes = ['phone' => $validated['phone'] ?? null];

        if ($canChangePassword && filled($validated['password'] ?? null)) {
            $changes['password'] = $validated['password'];
        }

        if ($request->hasFile('photo_profile')) {
            $newPhoto = $request->file('photo_profile')->store('profile-photos', 'public');
            $oldPhoto = $user->photo_profile;
            $changes['photo_profile'] = $newPhoto;

            $user->update($changes);

            if ($oldPhoto && $oldPhoto !== $newPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
        } else {
            $user->update($changes);
        }

        app(ActivityLogService::class)->record([
            'action' => 'UPDATE',
            'module' => 'profile',
            'description' => 'Memperbarui foto profil atau nomor WhatsApp',
            'target_type' => 'user_profile',
            'target_id' => $user->id,
        ]);

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui.');
    }
}
