<?php

namespace RCMan\Services;

use RCMan\Settings;
use RCMan\Support\Logger;

defined('ABSPATH') || exit;

/**
 * Sends bulk e-mail (newsletters) in batches through WP-Cron,
 * replacing Kiyomi's queued SendEmailToUsers job.
 */
final class MailQueue
{
    public const CRON_HOOK = 'rcman_process_mail_queue';
    private const OPTION = 'rcman_mail_queue';

    public function register(): void
    {
        add_action(self::CRON_HOOK, [$this, 'process']);
    }

    /**
     * @param string[] $recipients
     * @param int[] $attachmentIds Media library attachment IDs.
     */
    public function enqueue(array $recipients, string $subject, string $html, array $attachmentIds = []): void
    {
        $queue   = $this->queue();
        $queue[] = [
            'id'          => wp_generate_uuid4(),
            'recipients'  => array_values(array_unique(array_filter($recipients, 'is_email'))),
            'subject'     => $subject,
            'html'        => $html,
            'attachments' => array_map('intval', $attachmentIds),
            'created_at'  => current_time('mysql'),
        ];

        update_option(self::OPTION, $queue, false);
        $this->schedule();
    }

    public function process(): void
    {
        $queue = $this->queue();

        if (!$queue) {
            return;
        }

        $mailer    = new Mailer();
        $batchSize = max(1, (int) Settings::get('mail_batch_size'));
        $job       = &$queue[0];

        $attachments = [];
        foreach ($job['attachments'] as $id) {
            $path = get_attached_file($id);
            if ($path && is_file($path)) {
                $attachments[] = $path;
            }
        }

        $batch = array_splice($job['recipients'], 0, $batchSize);

        foreach ($batch as $email) {
            $mailer->sendHtml($email, $job['subject'], $job['html'], $attachments);
        }

        if (!$job['recipients']) {
            Logger::info('Nieuwsbrief verzonden', ['subject' => $job['subject']]);
            array_shift($queue);
        }

        unset($job);
        update_option(self::OPTION, $queue, false);

        if ($queue) {
            $this->schedule(MINUTE_IN_SECONDS);
        }
    }

    public function pendingCount(): int
    {
        return array_sum(array_map(static fn (array $job) => count($job['recipients']), $this->queue()));
    }

    private function schedule(int $delay = 0): void
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_single_event(time() + $delay, self::CRON_HOOK);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function queue(): array
    {
        $queue = get_option(self::OPTION, []);

        return is_array($queue) ? $queue : [];
    }

    public static function clear(): void
    {
        delete_option(self::OPTION);
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }
}
