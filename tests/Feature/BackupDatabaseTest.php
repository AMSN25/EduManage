<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

it('registers the backup:database command', function () {
    $commands = \Illuminate\Support\Facades\Artisan::all();

    expect($commands)->toHaveKey('backup:database');
});

it('schedules backup:database daily at 02:00', function () {
    $events = collect(Schedule::events());
    $backupEvent = $events->first(fn ($e) => str_contains($e->command, 'backup:database'));

    expect($backupEvent)->not->toBeNull();
});

it('creates backups disk entry in config', function () {
    $disks = config('filesystems.disks');

    expect($disks)->toHaveKey('backups');
    expect($disks['backups']['driver'])->toBe('local');
    expect($disks['backups']['root'])->toContain('backups');
});
