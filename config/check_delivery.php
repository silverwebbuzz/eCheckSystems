<?php

// Deluxe-style check delivery (client Point 5): payee gets a "Get Check" link instead of a PDF attachment.
return [
    // How long a "Get Check" link works. 90 matches "void after 90 days" printed on the check.
    'link_days' => (int) env('CHECK_LINK_DAYS', 90),

    // Help address shown on the Get Check pages and in the email. Set CHECK_SUPPORT_EMAIL to a real
    // inbox (the mail "from" address is often a no-reply address).
    'support_email' => env('CHECK_SUPPORT_EMAIL', env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS'))),

    // Email template id used for the "You have a check" email (editable in admin → Email Templates).
    'email_template_id' => 18,
];
