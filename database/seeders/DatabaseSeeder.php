<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder - نقطة انطلاق تهيئة البيانات
 *
 * ينظم ترتيب تشغيل جميع الـ Seeders بالتسلسل الصحيح:
 * 1. UsersSeeder          : المستخدمون الافتراضيون
 * 2. FournisseursSeeder   : الموردون
 * 3. CategoriesSeeder     : فئات المنتجات
 * 4. ProduitsSeeder       : المنتجات (500+)
 * 5. TablesSeeder         : طاولات المطعم
 * 6. RestaurantSettingsSeeder : إعدادات المطعم
 * 7. DocumentationSeeder  : إرشادات النظام
 * 8. DashboardStatsSeeder : بيانات تجريبية للوحة التحكم
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            FournisseursSeeder::class,
            MenuTemporaireV4Seeder::class,
            TablesSeeder::class,
            RestaurantSettingsSeeder::class,
            DocumentationSeeder::class,
            DashboardStatsSeeder::class,
        ]);
    }
}

