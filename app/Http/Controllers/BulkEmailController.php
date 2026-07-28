<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendBulkEmailToClients;

class BulkEmailController extends Controller
{
    public function create()
    {
        $clients = User::select('UserID', 'FirstName', 'LastName', 'email')->orderBy('FirstName')->whereNotNull('email')->get();
        return view('admin.bulk-email.create', compact('clients'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'client' => 'required',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $clients = $request->client;

        // Ensure it's always an array
        $clients = is_array($clients) ? $clients : [$clients];

        // Determine recipients
        if (in_array('all', $clients)) {
            $users = User::whereNotNull('Email')->where('Status', 'Active')->get();
        } else {
            $users = User::whereIn('UserID', $clients)->whereNotNull('Email')->where('Status', 'Active')->get();
        }

        foreach ($users as $user) {

            if (empty($user->Email)) {
                continue; // safety check
            }

            $content = 'Hello ' . trim($user->FirstName . ' ' . $user->LastName) . ',';

            try {
                Mail::to($user->Email)->queue(
                    new SendBulkEmailToClients(
                        $request->subject,
                        (object) [
                            'head' => '',
                            'content' => $content,
                            'body1' => $request->body,
                            'body2' => '',
                        ]
                    )
                );
            } catch (\Throwable $th) {
                // Log::error($th->getMessage());
            }

        }

        return back()->with('success', 'Emails sent successfully.');
    }

}
