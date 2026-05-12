<?php

namespace Database\Seeders;

use App\Enums\Ask;
use App\Enums\Role as EnumRole;
use App\Enums\Status;
use App\Models\TherapistProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class TherapistSeeder extends Seeder
{
    public array $therapists = [
        [
            'name'            => 'Sophea Chan',
            'email'           => 'sophea.chan@example.com',
            'phone'           => '0961000001',
            'username'        => 'sophea-chan',
            'branch_id'       => 1,
            'commission_rate' => 10.00,
            'status'          => 'available',
        ],
        [
            'name'            => 'Bopha Keo',
            'email'           => 'bopha.keo@example.com',
            'phone'           => '0961000002',
            'username'        => 'bopha-keo',
            'branch_id'       => 1,
            'commission_rate' => 10.00,
            'status'          => 'available',
        ],
        [
            'name'            => 'Dara Pich',
            'email'           => 'dara.pich@example.com',
            'phone'           => '0961000003',
            'username'        => 'dara-pich',
            'branch_id'       => 1,
            'commission_rate' => 12.00,
            'status'          => 'available',
        ],
        [
            'name'            => 'Sreyleak Noun',
            'email'           => 'sreyleak.noun@example.com',
            'phone'           => '0961000004',
            'username'        => 'sreyleak-noun',
            'branch_id'       => 1,
            'commission_rate' => 10.00,
            'status'          => 'available',
        ],
        [
            'name'            => 'Virak Sok',
            'email'           => 'virak.sok@example.com',
            'phone'           => '0961000005',
            'username'        => 'virak-sok',
            'branch_id'       => 1,
            'commission_rate' => 15.00,
            'status'          => 'available',
        ],
    ];

    public function run(): void
    {
        foreach ($this->therapists as $therapist) {
            $user = User::firstOrCreate(
                ['email' => $therapist['email']],
                [
                    'name'              => $therapist['name'],
                    'phone'             => $therapist['phone'],
                    'username'          => $therapist['username'],
                    'email_verified_at' => now(),
                    'password'          => bcrypt('123456'),
                    'branch_id'         => $therapist['branch_id'],
                    'status'            => Status::ACTIVE,
                    'country_code'      => '+855',
                    'is_guest'          => Ask::NO,
                ]
            );

            $user->assignRole(EnumRole::THERAPIST);

            TherapistProfile::firstOrCreate( 
                ['user_id' => $user->id],
                [
                    'commission_rate' => $therapist['commission_rate'],
                    'status'          => $therapist['status'],
                    'branch_id'       => $therapist['branch_id'],
                ]
            );
        }
    }
}
