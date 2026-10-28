<?php

return [
    'expires_minutes' => (int) env('LOGIN_OTP_EXPIRES_MINUTES', 5),
    'resend_seconds' => (int) env('LOGIN_OTP_RESEND_SECONDS', 60),
    'max_attempts' => (int) env('LOGIN_OTP_MAX_ATTEMPTS', 5),
    'bypass_emails' => array_values(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('LOGIN_OTP_BYPASS_EMAILS', 'admin@gmail.com'))
    ))),
];
