<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database
                            {--disk= : Filesystem disk to upload to (defaults to BACKUP_DISK env)}
                            {--keep=30 : Number of backup files to retain on the disk}';

    protected $description = 'Dump the database to a timestamped .sql file and upload it to a configured disk';

    public function handle(): int
    {
        $diskName = $this->option('disk') ?: config('backup.disk', env('BACKUP_DISK', 'backups'));
        $keep = (int) $this->option('keep');

        $timestamp = now()->format('Y-m-d_His');
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if (!$database) {
            $this->error("Could not determine database name for connection [{$connection}].");
            return self::FAILURE;
        }

        $filename = "backup_{$database}_{$timestamp}.sql";
        $tempPath = storage_path("app/{$filename}");

        $this->info("Dumping database [{$database}]...");

        try {
            $this->dumpDatabase($connection, $tempPath);
        } catch (\Exception $e) {
            $this->error("Database dump failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("Uploading {$filename} to disk [{$diskName}]...");

        try {
            $contents = file_get_contents($tempPath);
            Storage::disk($diskName)->put($filename, $contents);
        } catch (\Exception $e) {
            $this->error("Upload failed: {$e->getMessage()}");
            @unlink($tempPath);
            return self::FAILURE;
        } finally {
            @unlink($tempPath);
        }

        $this->info("Backup uploaded: {$filename}");

        // Prune old backups beyond --keep count
        $this->pruneOldBackups($diskName, $keep);

        return self::SUCCESS;
    }

    private function dumpDatabase(string $connection, string $path): void
    {
        $config = config("database.connections.{$connection}");

        match ($config['driver']) {
            'sqlite' => $this->dumpSqlite($config['database'], $path),
            'mysql' => $this->dumpMysql($config, $path),
            'pgsql' => $this->dumpPgsql($config, $path),
            default => throw new \RuntimeException("Unsupported driver: {$config['driver']}"),
        };
    }

    private function dumpSqlite(string $database, string $path): void
    {
        $escapedPath = escapeshellarg($path);
        $escapedDb = escapeshellarg($database);
        exec("sqlite3 {$escapedDb} .dump > {$escapedPath}", $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('sqlite3 dump failed with exit code ' . $exitCode);
        }
    }

    private function dumpMysql(array $config, string $path): void
    {
        $host = escapeshellarg($config['host'] ?? '127.0.0.1');
        $port = escapeshellarg($config['port'] ?? '3306');
        $database = escapeshellarg($config['database']);
        $username = escapeshellarg($config['username'] ?? '');
        $password = $config['password'] ?? '';

        $command = "mysqldump -h {$host} -P {$port} -u {$username}";
        if ($password !== '') {
            $command .= ' -p' . escapeshellarg($password);
        }
        $command .= " {$database} > " . escapeshellarg($path);

        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new \RuntimeException('mysqldump failed with exit code ' . $exitCode);
        }
    }

    private function dumpPgsql(array $config, string $path): void
    {
        $host = escapeshellarg($config['host'] ?? '127.0.0.1');
        $port = escapeshellarg($config['port'] ?? '5432');
        $database = escapeshellarg($config['database']);
        $username = escapeshellarg($config['username'] ?? '');

        $command = "pg_dump -h {$host} -p {$port} -U {$username} {$database} > " . escapeshellarg($path);
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('pg_dump failed with exit code ' . $exitCode);
        }
    }

    private function pruneOldBackups(string $diskName, int $keep): void
    {
        if ($keep <= 0) {
            return;
        }

        $files = Storage::disk($diskName)
            ->files('/')
            ->filter(fn ($file) => str_ends_with($file, '.sql'))
            ->sort()
            ->values();

        if ($files->count() <= $keep) {
            return;
        }

        $toDelete = $files->slice(0, $files->count() - $keep);
        foreach ($toDelete as $file) {
            Storage::disk($diskName)->delete($file);
            $this->line("  Pruned: {$file}");
        }
    }
}
