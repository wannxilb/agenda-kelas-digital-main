<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

/**
 * Guard agar command database yang destruktif tidak salah sasaran ke DB dev.
 */
class DatabaseSafetyProvider extends ServiceProvider
{
    /**
     * Command yang bisa menghapus/menimpa isi database.
     */
    protected const DESTRUCTIVE_COMMANDS = [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'db:wipe',
    ];

    /**
     * Command yang boleh jalan di env test/e2e hanya jika DB-nya aman.
     */
    protected const TEST_ENV_SENSITIVE_COMMANDS = [
        'migrate',
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'db:wipe',
        'db:seed',
        'serve',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            $command = (string) $event->command;

            if (
                ! in_array($command, self::DESTRUCTIVE_COMMANDS, true)
                && ! in_array($command, self::TEST_ENV_SENSITIVE_COMMANDS, true)
            ) {
                return;
            }

            $requestedEnv = $this->requestedEnvironment($event);
            $activeEnv = app()->environment();
            $targetEnv = $requestedEnv ?: $activeEnv;
            $connection = $this->resolvedConnection();

            if (in_array($targetEnv, ['testing', 'e2e'], true)) {
                $this->ensureTestDatabase($command, $targetEnv, $connection);

                return;
            }

            if (in_array($command, self::DESTRUCTIVE_COMMANDS, true)) {
                $this->ensureDestructiveCommandAllowed($command, $targetEnv, $connection);
            }
        });
    }

    /**
     * @return array{default:string, driver:string, database:string}
     */
    protected function resolvedConnection(): array
    {
        $default = (string) config('database.default', '');

        return [
            'default' => $default,
            'driver' => (string) config("database.connections.{$default}.driver", ''),
            'database' => (string) config("database.connections.{$default}.database", ''),
        ];
    }

    protected function requestedEnvironment(CommandStarting $event): ?string
    {
        $value = $event->input->getParameterOption('--env', null);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param  array{default:string, driver:string, database:string}  $connection
     */
    protected function ensureTestDatabase(string $command, string $environment, array $connection): void
    {
        if ($this->isSafeTestDatabase($connection, $environment)) {
            return;
        }

        throw new RuntimeException(
            'DATABASE SAFETY GUARD: command `'.$command.' --env='.$environment.'` dibatalkan. '
            .'Environment '.$environment.' diminta, tetapi koneksi database yang ter-resolve adalah '
            .$this->formatConnection($connection).'. '
            .'Ini berisiko menghapus database development. Pastikan file .env.'.$environment
            .' ada dan DB_DATABASE memakai database khusus test, misalnya agenda_kelas_test '
            .'atau database/e2e.sqlite.'
        );
    }

    /**
     * @param  array{default:string, driver:string, database:string}  $connection
     */
    protected function ensureDestructiveCommandAllowed(string $command, string $environment, array $connection): void
    {
        if ($this->destructiveOverrideEnabled()) {
            return;
        }

        if ($this->isSafeNamedTestDatabase($connection) || $this->isSafeSqliteTestDatabase($connection)) {
            return;
        }

        throw new RuntimeException(
            'DATABASE SAFETY GUARD: command destruktif `'.$command.'` dibatalkan pada environment '
            .$environment.'. Target database: '.$this->formatConnection($connection).'. '
            .'Command ini hanya boleh langsung jalan pada database test/e2e. '
            .'Kalau benar-benar ingin reset database dev, jalankan dengan env '
            .'APP_ALLOW_DESTRUCTIVE_DB_COMMANDS=true secara sadar.'
        );
    }

    /**
     * @param  array{default:string, driver:string, database:string}  $connection
     */
    protected function isSafeTestDatabase(array $connection, string $environment): bool
    {
        if ($environment === 'e2e') {
            return $this->isSafeE2eDatabase($connection);
        }

        return $this->isSafeNamedTestDatabase($connection) || $this->isSafeSqliteTestDatabase($connection);
    }

    /**
     * @param  array{default:string, driver:string, database:string}  $connection
     */
    protected function isSafeE2eDatabase(array $connection): bool
    {
        $database = str_replace('\\', '/', $connection['database']);

        return $connection['driver'] === 'sqlite' && str_ends_with($database, 'database/e2e.sqlite');
    }

    /**
     * @param  array{default:string, driver:string, database:string}  $connection
     */
    protected function isSafeNamedTestDatabase(array $connection): bool
    {
        $database = strtolower($connection['database']);

        return str_contains($database, 'test') || str_contains($database, 'testing');
    }

    /**
     * @param  array{default:string, driver:string, database:string}  $connection
     */
    protected function isSafeSqliteTestDatabase(array $connection): bool
    {
        if ($connection['driver'] !== 'sqlite') {
            return false;
        }

        $database = strtolower(str_replace('\\', '/', $connection['database']));

        return str_contains($database, 'test') || str_contains($database, 'e2e');
    }

    protected function destructiveOverrideEnabled(): bool
    {
        return filter_var(env('APP_ALLOW_DESTRUCTIVE_DB_COMMANDS', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  array{default:string, driver:string, database:string}  $connection
     */
    protected function formatConnection(array $connection): string
    {
        return $connection['default'].' / '.$connection['driver'].' / '
            .($connection['database'] !== '' ? $connection['database'] : '(kosong)');
    }
}
