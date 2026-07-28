<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'mail_host' => env('MAIL_HOST'),
            'mail_port' => env('MAIL_PORT'),
            'mail_username' => env('MAIL_USERNAME'),
            'mail_password' => env('MAIL_PASSWORD'),
            'mail_encryption' => env('MAIL_ENCRYPTION'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS'),
            'stripe_public' => env('STRIPE_PUBLIC'),
            'stripe_secret' => env('STRIPE_SECRET'),
            'admin_email' => env('ADMIN_EMAIL'),
        ];
        return view('admin.setting', compact('settings'));
    }
    public function updateSettings(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|string',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|string',
            'mail_from_address' => 'required|string',
            'stripe_public' => 'required|string',
            'stripe_secret' => 'required|string',
            'admin_email' => 'required|email',
        ]);

        // Update .env values using the helper function
        $this->update_env('MAIL_HOST', $validated['mail_host']);
        $this->update_env('MAIL_PORT', $validated['mail_port']);
        $this->update_env('MAIL_USERNAME', $validated['mail_username']);
        $this->update_env('MAIL_PASSWORD', $validated['mail_password']);
        $this->update_env('MAIL_ENCRYPTION', $validated['mail_encryption']);
        $this->update_env('MAIL_FROM_ADDRESS', $validated['mail_from_address']);
        $this->update_env('STRIPE_PUBLIC', $validated['stripe_public']);
        $this->update_env('STRIPE_SECRET', $validated['stripe_secret']);
        $this->update_env('ADMIN_EMAIL', $validated['admin_email']);

        // Clear the cached configuration
        Artisan::call('config:clear');

        return back()->with('success', 'Settings updated successfully!');
    }

    function update_env($key, $value)
    {
        $envFile = base_path('.env');
        $lines = file($envFile);
        $found = false;

        foreach ($lines as &$line) {
            // Check if the line already contains the key
            if (strpos($line, "$key=") === 0) {
                $line = "$key=$value\n"; // Replace the value
                $found = true;
                break;
            }
        }

        // If the key wasn't found, add it to the end of the file
        if (!$found) {
            $lines[] = "$key=$value\n";
        }

        // Write the modified content back to the .env file
        file_put_contents($envFile, implode('', $lines));
    }
}
