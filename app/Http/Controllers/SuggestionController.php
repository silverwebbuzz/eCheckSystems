<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suggestion;
use App\Mail\AdminSuggestionMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Str;

class SuggestionController extends Controller
{
    public function add()
    {

        return view('user.suggestions.add');
        //   return view('user.check.process_payment_check');
    }

    public function store(Request $request)
    {
        $request->validate([
            'section' => 'required',
            'description' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (preg_match('/<img[^>]+src="data:image/', $value)) {
                        $fail('Images are not allowed in the description.');
                    }
                },
            ],
        ]);

        $suggestion = Suggestion::create([
            'section' => $request->section,
            'user_id' => Auth::id(),
            'description' => $request->description
        ]);

        Mail::to(env('ADMIN_EMAIL'))->send(new AdminSuggestionMail(13,$suggestion));
        return back()->with('success', 'Suggestion sent successfully');
    }

    public function index()
    {

        return view('admin.suggestions.index');
    }

    public function list()
    {
        $suggestions = Suggestion::orderBy('id', 'desc')->get();

        return datatables()->of($suggestions)
            ->addIndexColumn()
            ->addColumn('actions', function ($row) {
                return '<a href="' . route('admin.suggestions.view', $row->id) . '" class="dropdown-item justify-content-center">
                                    <i class="ti ti-eye me-1"></i> View
                                </a>';
                // return '<a href="' . route('admin.suggestions.view', $row->id) . '" class="btn btn-primary">View</a>';
            })
            ->rawColumns(['description','actions'])
            ->make(true);
    }

    public function view($id)
    {
        $suggestion = Suggestion::find($id);
        return view('admin.suggestions.view', compact('suggestion'));
    }
}
