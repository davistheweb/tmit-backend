<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Full system administrator'],
            ['name' => 'lecturer', 'description' => 'Manages courses, scores, and communication with students'],
            ['name' => 'hod', 'description' => 'Approves departmental courses and results, monitors performance'],
            ['name' => 'exam_officer', 'description' => 'Verifies course registrations and approves results'],
            ['name' => 'bursar', 'description' => 'Handles payments, confirmations, and financial reports'],
            ['name' => 'registry', 'description' => 'Manages student records, admissions, and verification'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
