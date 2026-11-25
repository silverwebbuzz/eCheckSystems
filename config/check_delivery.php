<?php

// Deluxe-style check delivery (client Point 5): payee gets a "Get Check" link instead of a PDF attachment.
return [
    // How long a "Get Check" link works. 90 matches "void after 90 days" printed on the check.
    'link_days' => (int) env('CHECK_LINK_DAYS', 90),

    // Email template id used for the "You have a check" email (editable in admin → Email Templates).
    'email_template_id' => 18,
];
