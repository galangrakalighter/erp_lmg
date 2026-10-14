<?php

namespace App\Support;

use App\Models\User;

class DigitalMarketingPermissions
{
    private const ALL_WORKSPACE_TABS = [
        'market-research', 'branding', 'social-media-research', 'advertising-research',
        'content-plan', 'guide', 'analisis', 'explore', 'dokumen', 'timeline',
    ];

    public static function role(?User $user): string
    {
        if (! $user) return '';
        if ($user->isAdmin) return 'Super Admin';
        $user->loadMissing('userDivisions.role', 'userDivisions.division');
        return (string) optional($user->userDivisions->first(
            fn ($row) => strtolower(trim((string) $row->division?->name)) === 'digital marketing'
        ))->role?->role_name;
    }

    public static function isManager(string $role): bool
    {
        return in_array($role, ['Super Admin', 'Busines Unit Head', 'Digital Marketing Manager'], true);
    }

    public static function canManageContentPlan(string $role, string $contentSource): bool
    {
        return self::isManager($role)
            || ($role === 'Content Creator' && $contentSource === 'organic')
            || ($role === 'Sosial Media Spesialis' && $contentSource === 'organic')
            || ($role === 'Advertising' && $contentSource === 'paid');
    }

    public static function mirrorScopes(string $role): array
    {
        if (self::isManager($role)) {
            return [
                'market-research', 'branding', 'social-media-research',
                'advertising-research', 'content-plan-organic', 'content-plan-paid',
            ];
        }

        return match ($role) {
            'Digital Marketing' => ['market-research', 'branding'],
            'Sosial Media Spesialis' => ['social-media-research', 'content-plan-organic'],
            'Advertising' => ['advertising-research', 'content-plan-paid'],
            'Content Creator' => ['content-plan-organic'],
            default => [],
        };
    }

    public static function workspaceTabs(string $role): array
    {
        return self::ALL_WORKSPACE_TABS;
    }

    public static function readOnlyWorkspaceTabs(string $role): array
    {
        return match ($role) {
            'Digital Marketing' => ['social-media-research', 'advertising-research', 'content-plan', 'guide', 'analisis', 'explore', 'timeline'],
            'Sosial Media Spesialis' => ['market-research', 'branding', 'advertising-research', 'analisis', 'explore', 'timeline'],
            'Advertising' => ['market-research', 'branding', 'social-media-research', 'guide', 'analisis', 'explore', 'timeline'],
            'Content Creator' => ['market-research', 'branding', 'social-media-research', 'advertising-research', 'guide', 'analisis', 'explore', 'timeline'],
            default => [],
        };
    }
}
