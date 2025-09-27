<?php

namespace Database\Seeders;

use App\Models\Enrollee;
use App\Models\Fee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EnrolleeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample documents structure (consistent with our document system)
        $sampleDocuments = [
            [
                'type' => 'PDF',
                'filename' => 'birth_certificate.pdf',
                'path' => 'documents/sample_birth_certificate.pdf',
                'mime_type' => 'application/pdf',
                'size' => 245760,
                'uploaded_at' => now()->toISOString(),
                'status' => 'pending'
            ],
            [
                'type' => 'JPG',
                'filename' => 'form_137.jpg',
                'path' => 'documents/sample_form_137.jpg',
                'mime_type' => 'image/jpeg',
                'size' => 512000,
                'uploaded_at' => now()->toISOString(),
                'status' => 'verified'
            ],
            [
                'type' => 'PDF',
                'filename' => 'good_moral.pdf',
                'path' => 'documents/sample_good_moral.pdf',
                'mime_type' => 'application/pdf',
                'size' => 189440,
                'uploaded_at' => now()->toISOString(),
                'status' => 'pending'
            ]
        ];

        // Sample ID Photo (base64 encoded placeholder)
        $sampleIdPhoto = base64_encode('sample_id_photo_data');

        // Create diverse enrollee test data
        $enrollees = [
            [
                'application_id' => '25-001',
                'password' => Hash::make('25-001'),
                'lrn' => '123456789012',
                'enrollment_status' => 'pending',
                'academic_year' => '2025-2026',
                'id_photo' => $sampleIdPhoto,
                'id_photo_mime_type' => 'image/jpeg',
                'documents' => $sampleDocuments,
                'first_name' => 'Juan',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'suffix' => '',
                'date_of_birth' => Carbon::parse('2008-03-15'),
                'place_of_birth' => 'Manila, Philippines',
                'gender' => 'male',
                'nationality' => 'Filipino',
                'religion' => 'Roman Catholic',
                'contact_number' => '09123456789',
                'email' => 'juan.delacruz@email.com',
                'address' => '123 Rizal Street, Barangay San Jose',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'zip_code' => '1000',
                'grade_level_applied' => 'Grade 7',
                'strand_applied' => null,
                'student_type' => 'new',
                'father_name' => 'Pedro Dela Cruz',
                'father_occupation' => 'Engineer',
                'father_contact' => '09987654321',
                'mother_name' => 'Maria Santos Dela Cruz',
                'mother_occupation' => 'Teacher',
                'mother_contact' => '09876543210',
                'guardian_name' => 'Pedro Dela Cruz',
                'guardian_contact' => '09987654321',
                'last_school_type' => 'public',
                'last_school_name' => 'Manila Elementary School',
                'medical_history' => 'No known allergies or medical conditions',
                'payment_mode' => 'cash',
                'is_paid' => false,
                'total_fees_due' => 15000.00,
                'total_paid' => 0.00,
                'payment_completed_at' => null,
                'preferred_schedule' => Carbon::parse('2025-01-15'),
                'application_date' => Carbon::now()->subDays(5),
                'is_active' => true,
                'admin_notes' => 'New student application - pending document verification'
            ],
            [
                'application_id' => '25-002',
                'password' => Hash::make('25-002'),
                'lrn' => '123456789013',
                'enrollment_status' => 'approved',
                'academic_year' => '2025-2026',
                'approved_at' => Carbon::now()->subDays(1),
                'id_photo' => $sampleIdPhoto,
                'id_photo_mime_type' => 'image/jpeg',
                'documents' => array_map(function($doc) {
                    $doc['status'] = 'verified';
                    return $doc;
                }, $sampleDocuments),
                'first_name' => 'Maria',
                'middle_name' => 'Garcia',
                'last_name' => 'Rodriguez',
                'suffix' => '',
                'date_of_birth' => Carbon::parse('2006-07-22'),
                'place_of_birth' => 'Quezon City, Philippines',
                'gender' => 'female',
                'nationality' => 'Filipino',
                'religion' => 'Christian',
                'contact_number' => '09234567890',
                'email' => 'maria.rodriguez@email.com',
                'address' => '456 Bonifacio Avenue, Barangay Central',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'zip_code' => '1100',
                'grade_level_applied' => 'Grade 11',
                'strand_applied' => 'STEM',
                'student_type' => 'transferee',
                'father_name' => 'Carlos Rodriguez',
                'father_occupation' => 'Business Owner',
                'father_contact' => '09345678901',
                'mother_name' => 'Ana Garcia Rodriguez',
                'mother_occupation' => 'Nurse',
                'mother_contact' => '09456789012',
                'guardian_name' => 'Ana Garcia Rodriguez',
                'guardian_contact' => '09456789012',
                'last_school_type' => 'private',
                'last_school_name' => 'St. Mary\'s High School',
                'medical_history' => 'Asthma - requires inhaler during physical activities',
                'payment_mode' => 'online payment',
                'is_paid' => true,
                'total_fees_due' => 25000.00,
                'total_paid' => 25000.00,
                'payment_completed_at' => Carbon::now()->subDays(1),
                'preferred_schedule' => Carbon::parse('2025-01-20'),
                'application_date' => Carbon::now()->subDays(10),
                'is_active' => true,
                'admin_notes' => 'Excellent academic record - approved for STEM program'
            ],
            [
                'application_id' => '25-003',
                'password' => Hash::make('25-003'),
                'lrn' => '123456789014',
                'enrollment_status' => 'pending',
                'academic_year' => '2025-2026',
                'id_photo' => $sampleIdPhoto,
                'id_photo_mime_type' => 'image/jpeg',
                'documents' => array_map(function($doc, $index) {
                    if ($index === 0) $doc['status'] = 'rejected';
                    return $doc;
                }, $sampleDocuments, array_keys($sampleDocuments)),
                'first_name' => 'Jose',
                'middle_name' => 'Mercado',
                'last_name' => 'Rizal',
                'suffix' => 'Jr.',
                'date_of_birth' => Carbon::parse('2007-12-30'),
                'place_of_birth' => 'Calamba, Laguna',
                'gender' => 'male',
                'nationality' => 'Filipino',
                'religion' => 'Roman Catholic',
                'contact_number' => '09345678901',
                'email' => 'jose.rizal@email.com',
                'address' => '789 Mercado Street, Barangay Poblacion',
                'city' => 'Calamba',
                'province' => 'Laguna',
                'zip_code' => '4027',
                'grade_level_applied' => 'Grade 10',
                'strand_applied' => null,
                'student_type' => 'new',
                'father_name' => 'Jose Rizal Sr.',
                'father_occupation' => 'Farmer',
                'father_contact' => '09567890123',
                'mother_name' => 'Teodora Mercado',
                'mother_occupation' => 'Homemaker',
                'mother_contact' => '09678901234',
                'guardian_name' => 'Jose Rizal Sr.',
                'guardian_contact' => '09567890123',
                'last_school_type' => 'public',
                'last_school_name' => 'Calamba National High School',
                'medical_history' => null,
                'payment_mode' => 'cash',
                'is_paid' => false,
                'total_fees_due' => 12000.00,
                'total_paid' => 0.00,
                'payment_completed_at' => null,
                'preferred_schedule' => Carbon::parse('2025-02-01'),
                'application_date' => Carbon::now()->subDays(3),
                'is_active' => true,
                'admin_notes' => 'Scholarship applicant - requires document resubmission'
            ],
            [
                'application_id' => '25-004',
                'password' => Hash::make('25-004'),
                'lrn' => '123456789015',
                'enrollment_status' => 'enrolled',
                'academic_year' => '2025-2026',
                'approved_at' => Carbon::now()->subDays(5),
                'enrolled_at' => Carbon::now()->subDays(2),
                'id_photo' => $sampleIdPhoto,
                'id_photo_mime_type' => 'image/jpeg',
                'documents' => array_map(function($doc) {
                    $doc['status'] = 'verified';
                    return $doc;
                }, $sampleDocuments),
                'first_name' => 'Gabriela',
                'middle_name' => 'Cruz',
                'last_name' => 'Silang',
                'suffix' => '',
                'date_of_birth' => Carbon::parse('2005-03-19'),
                'place_of_birth' => 'Ilocos Sur, Philippines',
                'gender' => 'female',
                'nationality' => 'Filipino',
                'religion' => 'Iglesia ni Cristo',
                'contact_number' => '09456789012',
                'email' => 'gabriela.silang@email.com',
                'address' => '321 Silang Road, Barangay Libertad',
                'city' => 'Vigan',
                'province' => 'Ilocos Sur',
                'zip_code' => '2700',
                'grade_level_applied' => 'Grade 12',
                'strand_applied' => 'ABM',
                'student_type' => 'continuing',
                'father_name' => 'Diego Silang',
                'father_occupation' => 'Revolutionary Leader',
                'father_contact' => '09789012345',
                'mother_name' => 'Josefa Cruz',
                'mother_occupation' => 'Seamstress',
                'mother_contact' => '09890123456',
                'guardian_name' => 'Josefa Cruz',
                'guardian_contact' => '09890123456',
                'last_school_type' => 'public',
                'last_school_name' => 'Vigan National High School',
                'medical_history' => 'Hypertension - requires regular monitoring',
                'payment_mode' => 'installment',
                'is_paid' => true,
                'total_fees_due' => 22000.00,
                'total_paid' => 22000.00,
                'payment_completed_at' => Carbon::now()->subDays(3),
                'preferred_schedule' => Carbon::parse('2025-01-10'),
                'application_date' => Carbon::now()->subDays(15),
                'is_active' => true,
                'admin_notes' => 'Continuing student - excellent academic performance'
            ],
            [
                'application_id' => '25-005',
                'password' => Hash::make('25-005'),
                'lrn' => '123456789016',
                'enrollment_status' => 'rejected',
                'academic_year' => '2025-2026',
                'rejected_at' => Carbon::now()->subDays(2),
                'status_reason' => 'Incomplete documents and failed entrance examination',
                'id_photo' => $sampleIdPhoto,
                'id_photo_mime_type' => 'image/jpeg',
                'documents' => array_map(function($doc) {
                    $doc['status'] = 'rejected';
                    return $doc;
                }, $sampleDocuments),
                'first_name' => 'Andres',
                'middle_name' => 'Magsaysay',
                'last_name' => 'Bonifacio',
                'suffix' => '',
                'date_of_birth' => Carbon::parse('2008-11-30'),
                'place_of_birth' => 'Tondo, Manila',
                'gender' => 'male',
                'nationality' => 'Filipino',
                'religion' => 'Roman Catholic',
                'contact_number' => '09567890123',
                'email' => 'andres.bonifacio@email.com',
                'address' => '654 Bonifacio Street, Barangay Tondo',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'zip_code' => '1012',
                'grade_level_applied' => 'Grade 8',
                'strand_applied' => null,
                'student_type' => 'new',
                'father_name' => 'Santiago Bonifacio',
                'father_occupation' => 'Tailor',
                'father_contact' => '09678901234',
                'mother_name' => 'Catalina Magsaysay',
                'mother_occupation' => 'Vendor',
                'mother_contact' => '09789012345',
                'guardian_name' => 'Santiago Bonifacio',
                'guardian_contact' => '09678901234',
                'last_school_type' => 'public',
                'last_school_name' => 'Tondo Elementary School',
                'medical_history' => null,
                'payment_mode' => 'cash',
                'is_paid' => false,
                'total_fees_due' => 15000.00,
                'total_paid' => 0.00,
                'payment_completed_at' => null,
                'preferred_schedule' => Carbon::parse('2025-01-25'),
                'application_date' => Carbon::now()->subDays(8),
                'is_active' => false,
                'admin_notes' => 'Application rejected due to incomplete requirements'
            ]
        ];

        // Create enrollees and calculate fees
        foreach ($enrollees as $enrolleeData) {
            $enrollee = Enrollee::create($enrolleeData);
            
            // Calculate and update fees if not already set
            if ($enrollee->total_fees_due === null || $enrollee->total_fees_due == 0) {
                try {
                    $feeCalculation = Fee::calculateTotalFeesForGrade(
                        $enrollee->grade_level_applied, 
                        $enrollee->academic_year
                    );
                    
                    $enrollee->update([
                        'total_fees_due' => $feeCalculation['total_amount'] ?? 15000.00
                    ]);
                } catch (\Exception $e) {
                    // Fallback to default fee if calculation fails
                    $enrollee->update([
                        'total_fees_due' => 15000.00
                    ]);
                }
            }
        }

        $this->command->info('Created ' . count($enrollees) . ' enrollee test records with diverse statuses and data.');
    }
}
