<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBackupConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'database_id' => ['sometimes', 'required', 'exists:backup_databases,id'],
            'storage_provider' => ['sometimes', 'required', 'in:aws_s3,azure_blob,google_cloud,do_spaces,backblaze_b2,wasabi,minio,local'],
            'storage_path' => ['sometimes', 'required', 'string', 'max:500'],
            'storage_credentials' => ['sometimes', 'required', 'array'],
            'frequency' => ['sometimes', 'required', 'in:hourly,every_6_hours,every_12_hours,daily,weekly,monthly'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['sometimes', 'required', 'string', 'timezone'],
            'day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'day_of_month' => ['nullable', 'integer', 'between:1,31'],
            'keep_backups' => ['sometimes', 'required', 'integer', 'min:0'],
            'compression' => ['sometimes', 'required', 'in:zip,tar,tar_gz,tar_bz2,none'],
            'encryption_password' => ['nullable', 'string', 'min:8'],
            'excluded_tables' => ['nullable', 'array'],
            'excluded_tables.*' => ['string'],
            'delete_local_on_fail' => ['boolean'],
            'verify_backup' => ['boolean'],
            'custom_filename' => ['nullable', 'string', 'max:255'],
            
            // Notifications
            'notifications.email_on_success' => ['boolean'],
            'notifications.email_on_failure' => ['boolean'],
            'notifications.email_recipients' => ['nullable', 'array'],
            'notifications.email_recipients.*' => ['email'],
            'notifications.webhook_url' => ['nullable', 'url'],
            'notifications.webhook_headers' => ['nullable', 'array'],
            'notifications.slack_enabled' => ['boolean'],
            'notifications.slack_webhook' => ['nullable', 'url'],
            'notifications.discord_enabled' => ['boolean'],
            'notifications.discord_webhook' => ['nullable', 'url'],
        ];
    }
}
