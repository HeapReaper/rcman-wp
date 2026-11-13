<?php

namespace RCMan\Services;

use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Renders a template to PDF using dompdf (installed through Composer).
 */
final class PdfGenerator
{
    public static function isAvailable(): bool
    {
        return class_exists(\Dompdf\Dompdf::class);
    }

    /**
     * @param array<string, mixed> $data
     * @throws \RuntimeException When dompdf is not installed.
     */
    public static function render(string $template, array $data, string $orientation = 'portrait'): string
    {
        if (!self::isAvailable()) {
            throw new \RuntimeException(
                __('PDF bibliotheek ontbreekt. Voer "composer install" uit in de map van de RC Manager plugin.', 'rcman')
            );
        }

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('tempDir', get_temp_dir());

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml(View::capture($template, $data), 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return (string) $dompdf->output();
    }
}
