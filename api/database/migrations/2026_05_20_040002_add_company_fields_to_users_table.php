<?php

use App\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')
                ->constrained('companies')->nullOnDelete();
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('avatar', 500)->nullable()->after('phone');
            $table->string('status', 20)->default(UserStatus::Active->value)->after('avatar');
            $table->string('device_code', 100)->nullable()->unique()->after('status');
            $table->string('pin', 255)->nullable()->after('device_code');
            $table->timestamp('last_login_at')->nullable()->after('pin');
            $table->softDeletes();

            $table->index('company_id');
            $table->index('status');
            $table->index('device_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['device_code']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'company_id',
                'phone',
                'avatar',
                'status',
                'device_code',
                'pin',
                'last_login_at',
            ]);
        });
    }
};
