<?php

namespace RCMan\Services;

defined('ABSPATH') || exit;

/**
 * Private file storage inside wp-content/uploads/rcman (blocked from direct web access).
 */
final class FileStorage
{
    public const REPORTS = 'reports';
    public const EXPORTS = 'member-exports';

    public static function baseDir(): string
    {
        $uploads = wp_upload_dir(null, false);

        return trailingslashit($uploads['basedir']) . 'rcman';
    }

    public static function dir(string $folder): string
    {
        return self::baseDir() . '/' . $folder;
    }

    public static function ensureProtectedDirectories(): void
    {
        foreach ([self::baseDir(), self::dir(self::REPORTS), self::dir(self::EXPORTS)] as $dir) {
            wp_mkdir_p($dir);

            if (!file_exists($dir . '/index.php')) {
                file_put_contents($dir . '/index.php', "<?php\n// Silence is golden.\n");
            }
        }

        $htaccess = self::baseDir() . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        }
    }

    public static function put(string $folder, string $fileName, string $contents): bool
    {
        self::ensureProtectedDirectories();

        return file_put_contents(self::path($folder, $fileName), $contents) !== false;
    }

    public static function path(string $folder, string $fileName): string
    {
        return self::dir($folder) . '/' . sanitize_file_name(basename($fileName));
    }

    public static function exists(string $folder, string $fileName): bool
    {
        return is_file(self::path($folder, $fileName));
    }

    public static function delete(string $folder, string $fileName): void
    {
        $path = self::path($folder, $fileName);

        if (is_file($path)) {
            wp_delete_file($path);
        }
    }

    /**
     * Streams a stored file to the browser as a download and exits.
     */
    public static function download(string $folder, string $fileName): void
    {
        $path = self::path($folder, $fileName);

        if (!is_file($path)) {
            wp_die(esc_html__('Bestand niet gevonden.', 'rcman'), '', ['response' => 404, 'back_link' => true]);
        }

        nocache_headers();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));

        readfile($path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
        exit;
    }

    /**
     * Returns a unique file name within a folder (appends -2, -3, ... when needed).
     */
    public static function uniqueName(string $folder, string $fileName): string
    {
        self::ensureProtectedDirectories();

        return wp_unique_filename(self::dir($folder), sanitize_file_name($fileName));
    }

    public static function deleteAll(): void
    {
        $base = self::baseDir();

        if (!is_dir($base)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : wp_delete_file($file->getPathname());
        }

        rmdir($base);
    }
}
