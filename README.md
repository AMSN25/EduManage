<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About EduManage BD

EduManage BD is a multi-tenant SaaS for Schools, Colleges, and Madrasas in Bangladesh.

## Database Backups

The `backup:database` Artisan command dumps the database to a timestamped `.sql` file and uploads it to a configured filesystem disk.

### Environment Variables

| Variable | Default | Description |
|---|---|---|
| `BACKUP_DISK` | `backups` | Filesystem disk name (see `config/filesystems.php`) |
| `BACKUP_DISK_DRIVER` | `local` | Driver for the backups disk (`local` or `s3`) |

For **local** backups (default), `.sql` files are stored in `storage/app/backups/`.

For **S3** backups, set `BACKUP_DISK_DRIVER=s3` and configure the `AWS_*` variables already present in `.env.example`.

### Scheduling

The backup runs daily at 2:00 AM via the task scheduler. Ensure your server's cron runs:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Manual Usage

```bash
php artisan backup:database
php artisan backup:database --disk=s3
php artisan backup:database --keep=60
```

### Restore

Restoring from a backup is a **manual step** for the person deploying this application. It has not been automated or tested here. To restore:

1. Place the `.sql` file on the server.
2. Run: `sqlite3 database/database.sqlite < backup.sql` (SQLite) or import into your MySQL/PostgreSQL instance.

## Contributing

Thank you for considering contributing to the EduManage BD project!

## License

The EduManage BD application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
