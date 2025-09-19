<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Lecturer
            ['name' => 'view_assigned_courses', 'description' => 'View assigned courses & class lists'],
            ['name' => 'upload_scores', 'description' => 'Upload continuous assessment and exam scores'],
            ['name' => 'message_students', 'description' => 'Send messages to students in assigned courses'],

            // HOD
            ['name' => 'approve_course_lists', 'description' => 'Approve departmental course lists for each semester'],
            ['name' => 'monitor_registration', 'description' => 'Monitor course registration status of students'],
            ['name' => 'approve_results', 'description' => 'Approve results before sending to exam officer'],
            ['name' => 'generate_dept_reports', 'description' => 'Generate departmental academic performance reports'],

            // Exam Officer
            ['name' => 'verify_registration', 'description' => 'Access and verify course registration data'],
            ['name' => 'approve_final_registration', 'description' => 'Approve final course registration lists'],
            ['name' => 'verify_results', 'description' => 'Verify and approve results uploaded by lecturers and HOD'],
            ['name' => 'generate_exam_reports', 'description' => 'Generate result summaries and exam attendance sheets'],

            // Bursar / Finance
            ['name' => 'monitor_payments', 'description' => 'Monitor all payments (fees, dues, hostel, etc.)'],
            ['name' => 'approve_payments', 'description' => 'Approve or confirm payments before granting portal access'],
            ['name' => 'generate_financial_reports', 'description' => 'Generate financial reports by dept/semester/overall'],
            ['name' => 'manage_payment_complaints', 'description' => 'Manage payment-related complaints or adjustments'],

            // Registry
            ['name' => 'manage_student_records', 'description' => 'Manage student records from admission to graduation'],
            ['name' => 'export_import_admissions', 'description' => 'Export and import admission data for JAMB verification'],
            ['name' => 'publish_admission_lists', 'description' => 'Publish approved admission lists'],
            ['name' => 'generate_admission_number', 'description' => 'Generate admission numbers for students'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
