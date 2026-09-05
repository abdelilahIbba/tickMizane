<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'business_name')->update(['value' => 'Oussoul House']);
            DB::table('settings')->where('key', 'business_address')->update(['value' => 'Oussoul House, Restaurant & Hotel']);
            DB::table('settings')->where('key', 'business_phone')->update(['value' => '06-60-43-27-86']);
            DB::table('settings')->where('key', 'business_email')->update(['value' => 'contact@oussoulhouse.ma']);
            DB::table('settings')->where('key', 'receipt_header')->update(['value' => "Oussoul House\nRESTAURANT & HOTEL\n06-60-43-27-86"]);
            DB::table('settings')->where('key', 'receipt_footer')->update(['value' => 'Merci de votre visite !']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
