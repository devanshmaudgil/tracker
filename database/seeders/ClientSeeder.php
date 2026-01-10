<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            'Mobile Com',
            'CPPIB',
            'CBRE',
            'Metrolinx',
            'CIPPB',
            'Atriano',
            'AT&T',
            'Intellect',
            'Wells Fargo / FNB',
        ];

        foreach ($clients as $clientName) {
            Client::firstOrCreate(
                ['client' => $clientName],
                ['client' => $clientName]
            );
        }
    }
}
