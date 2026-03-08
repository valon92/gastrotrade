<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class ClientSetPassword extends Command
{
    protected $signature = 'client:set-password {email : Email-i i klientit} {password : Fjalëkalimi i ri}';

    protected $description = 'Vendos ose ndryshon fjalëkalimin e një klienti sipas email-it (për rregullim nga terminal)';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $password = $this->argument('password');

        if (strlen($password) < 6) {
            $this->error('Fjalëkalimi duhet të ketë të paktën 6 karaktere.');
            return self::FAILURE;
        }

        $client = Client::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$client) {
            $this->error("Klienti me email {$email} nuk u gjet.");
            return self::FAILURE;
        }

        $client->password = $password;
        $client->save();

        $this->info("Fjalëkalimi u vendos me sukses për klientin: {$email}");
        return self::SUCCESS;
    }
}
