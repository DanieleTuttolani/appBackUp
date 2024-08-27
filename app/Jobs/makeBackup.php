<?php

namespace App\Jobs;

use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class makeBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $domainID;
    public function __construct(int $domainID)
    {
        $this->domainID = $domainID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Starting backup process for domain ID: {$this->domainID}");

        // Recupera il dominio target
        $target = Domain::where('id', $this->domainID)->first();

        if (!$target) {
            Log::error("Domain not found for ID: {$this->domainID}");
            return;
        }

        // Parametri del database
        $dbHost = $target->ip;
        $dbName = $target->database_name;
        $dbUser = $target->user_name;
        $domName = $target->domain_name;
        $dbPass = $target->password;

        Log::info("Configuration loaded for domain: {$domName}");

        // Inserisco il db nelle configurazioni in modo dinamico ma temporaneamente
        config([
            'database.connections.external' => [
                'driver' => 'mysql',
                'host' => $dbHost,
                'database' => $dbName,
                'username' => $dbUser,
                'password' => $dbPass,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ]);

        $backupPath = storage_path("app/backup/{$domName}.sql");

        if (!file_exists($backupPath)) {
            Log::info("Creating backup file at: {$backupPath}");
            touch($backupPath);
        } else {
            Log::info("Backup file already exists at: {$backupPath}, will be overwritenn.");
        }

        // ricordati di cambiare il percorso del mysqldump.exe dipendentemente da dove è collocato nel server
        $command = sprintf(
            '"C:\\MAMP\\bin\\mysql\\bin\\mysqldump.exe" --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbHost),
            escapeshellarg($dbName),
            escapeshellarg($backupPath)
        );

        Log::info("Running backup command: {$command}");

        $output = null;
        $result = null;
        exec($command, $output, $result);

        if ($result === 0) {
            Log::info("Backup successfully created at: {$backupPath}");
        } else {
            Log::error("Backup failed with result code: {$result}");
            Log::error("Command output: " . implode("\n", $output));
        }
    }
}
