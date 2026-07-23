<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class PassportClientSeeder extends Seeder
{
    /**
     * Create the password grant client used by the frontend to log users in
     * directly against Passport's native `/oauth/token` endpoint.
     */
    public function run(ClientRepository $clients): void
    {
        $existing = Passport::client()->newQuery()
            ->get()
            ->first(fn ($client) => $client->hasGrantType('password'));

        if ($existing) {
            $this->command?->info("El cliente password grant ya existe (id: {$existing->id}).");

            return;
        }

        $client = $clients->createPasswordGrantClient(
            name: config('app.name').' Password Grant Client',
            provider: 'users',
        );

        $this->command?->info('Cliente password grant creado.');
        $this->command?->info("Client ID: {$client->id}");
        $this->command?->info($client->plainSecret
            ? "Client Secret: {$client->plainSecret}"
            : 'Client Secret: (ninguno, cliente público)');
    }
}
