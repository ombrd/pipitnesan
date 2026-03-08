<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ao_promo_members_tables', function (Blueprint $table) {
        Schema::table('account_officers', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('name');
            $table->date('active_date')->nullable()->after('phone');
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete()->after('id');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->string('id_card_number')->nullable()->after('email');
            $table->decimal('total_payment', 15, 2)->nullable()->after('id_card_number');
            $table->string('account_officer_code')->nullable()->after('marketing_code');
            $table->string('status')->default('active')->after('branch_id');
            $table->foreignId('promotion_id')->nullable()->constrained()->nullOnDelete()->after('branch_id');
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ao_promo_members_tables', function (Blueprint $table) {
        Schema::table('account_officers', function (Blueprint $table) {
            $table->dropColumn(['phone', 'active_date']);
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['code', 'branch_id']);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn([
                'id_card_number', 'total_payment', 'account_officer_code', 'status', 'promotion_id'
            ]);
        });
        });
    }
};
