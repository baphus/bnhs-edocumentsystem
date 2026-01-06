<?php

namespace Database\Seeders;

use App\Models\Track;
use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tracks = [
            'Academic Track' => [
                'STEM' => 'Science, Technology, Engineering, and Mathematics (STEM)',
                'ABM' => 'Accountancy, Business, and Management (ABM)',
                'HUMSS' => 'Humanities and Social Sciences (HUMSS)',
                'GAS' => 'General Academic Strand (GAS)',
            ],
            'Technical-Vocational-Livelihood Track' => [
                'TVL-HE' => 'TVL - Home Economics',
                'TVL-ICT' => 'TVL - Information and Communications Technology',
                'TVL-IA' => 'TVL - Industrial Arts',
                'TVL-AFA' => 'TVL - Agri-Fishery Arts',
            ],
            'Sports Track' => [
                'Sports' => 'Sports Track',
            ],
            'Arts and Design Track' => [
                'Arts' => 'Arts and Design Track',
            ],
        ];

        foreach ($tracks as $category => $strands) {
            foreach ($strands as $code => $name) {
                Track::firstOrCreate(
                    ['code' => $code],
                    [
                        'category' => $category,
                        'name' => $name,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
