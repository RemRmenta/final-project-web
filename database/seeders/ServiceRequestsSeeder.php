<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceRequest;

class ServiceRequestsSeeder extends Seeder
{
    public function run()
    {
        ServiceRequest::create([
            'resident_id'=>3,
            'title'=>'Water leak at Main St.',
            'description'=>'There is a leak near Barangay hall.',
            'address'=>'Zone 1, Bulan, Sorsogon',
            'priority'=>'high'
        ]);
    }
}
