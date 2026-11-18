<?php

return [

    /*
    | Where archives live. Kept outside the web root; the directory is forced to 0700
    | and every archive to 0600.
    */
    'directory' => env('BACKUP_DIRECTORY', storage_path('app/backups')),

    /*
    | Secret used to AES-256 encrypt every archive entry. Required: backups are
    | refused while it is missing or shorter than 32 characters. Keep a copy offline,
    | since archives cannot be opened (or restored) without it.
    | Generate one with: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
    */
    'archive_password' => env('BACKUP_ARCHIVE_PASSWORD'),

    /*
    | How long (seconds) a backup-specific password confirmation stays valid for
    | create / download / delete. Deliberately shorter than auth.password_timeout.
    */
    'password_confirmation_timeout' => (int) env('BACKUP_PASSWORD_CONFIRM_SECONDS', 900),

    // Lifetime (seconds) of the signed download link handed to the browser
    'download_link_ttl' => 60,

    // Archive sanity limits checked before anything is read out of an archive
    'max_entries' => 200000,
    'max_uncompressed_bytes' => (int) env('BACKUP_MAX_UNCOMPRESSED_BYTES', 20 * 1024 ** 3),

    // mysqldump / mysql runtime (seconds)
    'process_timeout' => 3600,

    'binaries' => [
        'mysqldump' => env('BACKUP_MYSQLDUMP_BINARY', 'mysqldump'),
        'mysql' => env('BACKUP_MYSQL_BINARY', 'mysql'),
    ],
];
