<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        
        if ($request->ajax()) {
            $emailTemplates = EmailTemplate::all();

            return datatables()->of($emailTemplates)
                ->addIndexColumn()
                ->addColumn('actions', function ($row) {
                    // Dynamically build URLs for the edit and delete actions
                    $editUrl = route('admin.email-template-edit', ['id' => $row->id]);

                    return '
                        <div class="d-flex">
                            <a href="' . $editUrl . '" class="dropdown-item">
                                <i class="ti ti-pencil me-1"></i> Edit
                            </a>
                        </div>';
                })
                ->rawColumns(['actions']) // Specify which columns have HTML content
                ->make(true); // Send response to DataTables
        }
        return view('admin.emails.index');
    }

    public function edit($id) 
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $emailTemplates = EmailTemplate::find($id);
        // dd($emailTemplates);
        return view('admin.emails.edit', compact('emailTemplates'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required' ,
            'subject' => 'required',
            'head' => 'required',
            'content' => 'required',
            'subject' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $emailTemplates = EmailTemplate::find($id);
        $emailTemplates->name = $request->name;
        $emailTemplates->subject = $request->subject;
        $emailTemplates->head = $request->head;
        $emailTemplates->content = $request->content;
        $emailTemplates->body1 = $request->body1;
        $emailTemplates->body2 = $request->body2;
        $emailTemplates->save();

        return redirect()->route('admin.email-template')->with('success', 'Email templates successfully');
    }
}
