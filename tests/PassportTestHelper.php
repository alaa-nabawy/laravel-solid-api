<?php

namespace Tests;

use App\Models\Passport\Client;
use Illuminate\Support\Facades\DB;

trait PassportTestHelper
{
    protected function setUpPassport()
    {
        // Create personal access client for testing
        $this->createPersonalAccessClient();
    }

    protected function createPersonalAccessClient()
    {
        // Create client directly in database
        $client = Client::create([
            'name'          => config('app.name') . ' Personal Access Client',
            'secret'        => 'secret', // pragma: allowlist secret
            'redirect_uris' => ['http://localhost'],
            'grant_types'   => ['personal_access'],
            'revoked'       => false,
        ]);

        // Create personal access client record using DB facade
        DB::table('oauth_personal_access_clients')->insert([
            'client_id'  => $client->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
