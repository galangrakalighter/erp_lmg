<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class DigitalMarketingPermissions
{
    private const ALL_WORKSPACE_TABS = [
        'market-research', 'branding', 'social-media-research', 'advertising-research',
        'content-plan', 'guide', 'analisis', 'explore', 'dokumen', 'timeline',
    ];

    public static function role(?Authenticatable $user): string
    {
        if (! $user) return '';
        if ((bool) data_get($user, 'isAdmin', false)) return 'Super Admin';

        return (string) (DB::table('user_division')
            ->join('divisions', 'divisions.id', '=', 'user_division.division_id')
            ->join('roles', function ($join): void {
                $join->on('roles.id', '=', 'user_division.role_id')
                    ->on('roles.division_id', '=', 'user_division.division_id');
            })
            ->where('user_division.user_id', $user->getAuthIdentifier())
            ->whereRaw('LOWER(TRIM(divisions.name)) = ?', ['digital marketing'])
            ->value('roles.role_name') ?? '');
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
