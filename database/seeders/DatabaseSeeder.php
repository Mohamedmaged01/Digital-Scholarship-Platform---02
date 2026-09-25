<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\DefaultContent;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DefaultContent::resetTracks();
        DefaultContent::resetUniversities();
        DefaultContent::resetStations();
        DefaultContent::resetNews();
        DefaultContent::resetCoreKnowledge();

        // حسابات تجريبية — غيّر كلمات المرور قبل النشر
        $accounts = [
            ['name' => 'مدير النظام', 'email' => 'admin@kasp.gov.sa', 'role' => 'admin', 'password' => 'Admin@2026'],
            ['name' => 'محرّر المحتوى', 'email' => 'editor@kasp.gov.sa', 'role' => 'editor', 'password' => 'Editor@2026'],
            ['name' => 'مطّلع', 'email' => 'viewer@kasp.gov.sa', 'role' => 'viewer', 'password' => 'Viewer@2026'],
        ];

        foreach ($accounts as $a) {
            User::updateOrCreate(['email' => $a['email']], $a + ['is_active' => true]);
        }
    }
}
