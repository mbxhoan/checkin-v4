<?php

namespace App\Enums;

enum SystemRole: string
{
    case SystemAdmin = 'system_admin';
    case SystemAudit = 'system_audit';
    case SystemSupport = 'system_support';
    case CompanyAdmin = 'company_admin';
    case CompanyManager = 'company_manager';
    case CompanyUser = 'company_user';
    case Scanner = 'scanner';

    public function isSystemLevel(): bool
    {
        return in_array($this, [
            self::SystemAdmin,
            self::SystemAudit,
            self::SystemSupport,
        ]);
    }

    public function isCompanyLevel(): bool
    {
        return in_array($this, [
            self::CompanyAdmin,
            self::CompanyManager,
            self::CompanyUser,
            self::Scanner,
        ]);
    }

    public function label(): string
    {
        return match ($this) {
            self::SystemAdmin => 'System Administrator',
            self::SystemAudit => 'System Auditor',
            self::SystemSupport => 'System Support',
            self::CompanyAdmin => 'Company Administrator',
            self::CompanyManager => 'Company Manager',
            self::CompanyUser => 'Company User',
            self::Scanner => 'Scanner Device',
        };
    }
}
