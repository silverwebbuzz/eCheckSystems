<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\UserSignature;
use Carbon\Carbon;

class SignController extends Controller
{
    public function get(Request $request)
    {
        if ($request->ajax()) {
            $signature = UserSignature::where('UserID', Auth::id())->get();

            return datatables()->of($signature)
                ->addIndexColumn()
                ->addColumn('Sign', function ($row) {
                    if(!empty($row->Sign)) {
                        return '<img src="' . asset('sign/' . $row->Sign) . '" alt="Sign" style="width: 90px;">';
                    } else {
                        return '<img src="' . asset('assets/img/empty.jpg') . '" alt="Sign" style="width: 90px;">';
                    }
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('edit_sign', ['id' => $row->Id]);
                    $deleteUrl = route('delete_sign', ['id' => $row->Id]);
                    return '<div style="display: flex;width: 56%">
                                <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i> Edit
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                data-bs-target="#delete' . $row->Id . '">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </a>
                            </div>
                            <div class="modal fade" id="delete' . $row->Id . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Signature</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this Signature?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                                                ' . csrf_field() . '
                                                ' . method_field('DELETE') . '
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                })
                ->rawColumns(['Sign','actions'])
                ->make(true);
        }
    }

    public function create()
    {
        return view('user.sign_add');
    }

    public function edit($id)
    {
        $userSignature = UserSignature::find($id);
        return view('user.sign_edit', compact('userSignature'));
    }

    public function addupdate(Request $request)
    {
        if(empty($request->id)) {
            $rules = [
                'name' => 'required',
                'signature' => 'required',
            ];
        } else {
            $rules = [
                'name' => 'required',
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()]);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        if(!empty($request->id)) {
            $userSignature = UserSignature::find($request->id);  
            $message = 'Signature updated successfully.';  
        } else {
            $userSignature = new UserSignature();
            $message = 'Signature added successfully.';
        }
        $userSignature->UserId = Auth::id();

        $fileName = '';
        if(!empty($request->signature)) {
            $folderPath = public_path('sign/');

            $image_parts = explode(";base64,", $request->signature);
            $image_type_aux = explode("image/", $image_parts[0]);

            if(!empty($image_type_aux[1])) {
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = uniqid() . '.'.$image_type;
                $file = $folderPath . $fileName;

                file_put_contents($file, $image_base64);
            }
        }
        $userSignature->Name = $request->name;
        $userSignature->Sign = $fileName;

        $userSignature->save();

        if ($request->ajax()) {
            return response()->json(['success' => true,'signature' => $userSignature]);
        } else {
            return redirect()->route('get_web_forms')->with('sign_success', $message);
        }
    }

    public function delete($id)
    {
        $userSignature = UserSignature::find($id);
        $userSignature?->delete();

        return redirect()->route('get_web_forms')->with('sign_success', 'Signature deleted successfully.');
    }
    
}
