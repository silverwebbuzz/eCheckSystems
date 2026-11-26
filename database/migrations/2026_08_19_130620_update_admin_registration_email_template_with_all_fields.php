<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')->where('id', 10)->update([
            'body1' => '<table style="border: 2px solid #dc3545; border-radius: 8px; border-collapse: collapse; width: 100%; max-width: 400px; margin: 10px 0;">
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Package:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ plan }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Name:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ name }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Email:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ email }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Phone:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ phone }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Company:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ company }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Address:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ address }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">City:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ city }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">State:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ state }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Zip:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ zip }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Timezone:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ timezone }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600;">IP Address:</td><td style="padding: 8px 12px;">{{ ip_address }}</td></tr>
</table>',
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('id', 10)->update([
            'body1' => '<table style="border: 2px solid #dc3545; border-radius: 8px; border-collapse: collapse; width: 100%; max-width: 400px; margin: 10px 0;">
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Package:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ plan }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600; border-bottom: 1px solid #eee;">Name:</td><td style="padding: 8px 12px; border-bottom: 1px solid #eee;">{{ name }}</td></tr>
<tr><td style="padding: 8px 12px; font-weight: 600;">Email:</td><td style="padding: 8px 12px;">{{ email }}</td></tr>
</table>',
        ]);
    }
};
