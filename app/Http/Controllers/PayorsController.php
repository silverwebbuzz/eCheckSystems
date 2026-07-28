<?php

namespace App\Http\Controllers;

use App\Models\HowItWork;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Payors;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\User;

class PayorsController extends Controller
{
    public function client_index(Request $request)
    {

        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {

            $query = Payors::where('UserID', Auth::id())
                ->whereIn('Type', ['Payee']);

            if (isset($request->status)) {
                $query->where('status', $request->status);
            }

            $payors = $query->get();

            return datatables()->of($payors)
                ->addIndexColumn()
                ->addColumn('Name', function ($row) {
                    return '
                            <div class="d-flex gap-2">
                                <span>' . $row->Name . '</span>
                                <a href="' . route('check_history', ['entity_id' => $row->EntityID]) . '">
                                    <i class="menu-icon tf-icons ti ti-map cursor-pointer"></i>
                                </a>
                            </div>
                        ';
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge ' .
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">' . $row->Status . '</span>';
                })
                ->addColumn('CreatedAt', function ($row) {
                    return User::user_timezone($row->CreatedAt, 'm/d/Y');
                })
                ->addColumn('UpdatedAt', function ($row) {
                    return User::user_timezone($row->UpdatedAt, 'm/d/Y');
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('user.payee.edit', ['id' => $row->EntityID]);
                    $deleteUrl = route('user.payee.delete', ['id' => $row->EntityID]);

                    return '<div class="d-flex">
                                <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i>
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                data-bs-target="#delete' . $row->EntityID . '">
                                    <i class="ti ti-trash me-1"></i>
                                </a>
                            </div>
                            <div class="modal fade" id="delete' . $row->EntityID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Payor</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this Payor?</p>
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

                ->rawColumns(['Name', 'status', 'actions'])
                ->make(true);
        }
        $how_it_works = HowItWork::select('section', 'link')->where('status', 'Active')->pluck('link', 'section');

        return view('user.Payee.index', compact('how_it_works'));
    }

    public function vendor_index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {

            $query = Payors::where('UserID', Auth::id())
                ->whereIn('Type', ['Payor', 'Both']);

            if (isset($request->status)) {
                $query->where('status', $request->status);
            }

            $payors = $query->get();

            return datatables()->of($payors)
                ->addIndexColumn()
                ->addColumn('Name', function ($row) {
                    return '
                            <div class="d-flex gap-2">
                                <span>' . $row->Name . '</span>
                                <a href="' . route('check_history', ['entity_id' => $row->EntityID]) . '">
                                    <i class="menu-icon tf-icons ti ti-map cursor-pointer"></i>
                                </a>
                            </div>
                        ';
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge ' .
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">' . $row->Status . '</span>';
                })
                ->addColumn('CreatedAt', function ($row) {
                    return User::user_timezone($row->CreatedAt, 'm/d/Y');
                })
                ->addColumn('UpdatedAt', function ($row) {
                    return User::user_timezone($row->UpdatedAt, 'm/d/Y');
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('user.payors.edit', ['id' => $row->EntityID]);
                    $deleteUrl = route('user.payors.delete', ['id' => $row->EntityID]);

                    return '<div class="d-flex">
                                <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i>
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                data-bs-target="#delete' . $row->EntityID . '">
                                    <i class="ti ti-trash me-1"></i>
                                </a>
                            </div>
                            <div class="modal fade" id="delete' . $row->EntityID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Payor</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this Payor?</p>
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

                ->rawColumns(['Name', 'status', 'actions'])
                ->make(true);
        }

        $how_it_works = HowItWork::select('section', 'link')->where('status', 'Active')->pluck('link', 'section');

        return view('user.Payors.index', compact('how_it_works'));
    }

    public function payor_create()
    {
        $type = 'Payors';
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }
        return view('user.' . $type . '.new');
    }

    public function payor_store(Request $request)
    {
        $type = 'Payors';
        $category = $request->category;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => [
                'nullable',
                'email',
                Rule::unique('Entities')
                    ->where(function ($query) use ($category) {
                        return $query->where('category', $category)
                            ->where('Type', 'Payor')
                            ->where('UserID', Auth::id());
                    })
            ],
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'bank_name' => 'required',
            'routing_number' => 'required|digits:9',
            'account_number' => 'required|numeric',
            'status' => 'required',
            'category' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $payors_type = $request->type;
        // if(!empty($request->same_as) && $request->same_as == 'on') {
        //     $payors_type = 'Both';
        // }

        $payor = new Payors();

        $payor->Name = $request->name;
        $payor->UserID = Auth::id();
        $payor->Address1 = $request->address1;
        $payor->Address2 = $request->address2;
        $payor->City = $request->city;
        $payor->State = $request->state;
        $payor->Zip = $request->zip;
        $payor->Email = $request->email;
        $payor->BankName = $request->bank_name;
        $payor->RoutingNumber = $request->routing_number;
        $payor->AccountNumber = $request->account_number;
        $payor->Status = $request->status;
        $payor->Category = $request->category;
        $payor->Type = $payors_type;

        $payor->save();


        return redirect()->route('user.' . $type)->with('success', $type . ' added successfully');
    }

    public function payor_edit($id)
    {
        $type = 'Payors';
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }
        $payor = Payors::find($id);
        return view('user.' . $type . '.edit', compact('payor'));
    }

    public function payor_update(Request $request, $id)
    {
        $type = 'Payors';
        $category = $request->category;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => [
                'nullable',
                'email',
                Rule::unique('Entities')
                    ->where(function ($query) use ($category) {
                        return $query->where('category', $category)
                            ->where('Type', 'Payor')
                            ->where('UserID', Auth::id());
                    })
                    ->ignore($id, 'EntityID')
            ],
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'bank_name' => 'required',
            'phone_number' => 'nullable|regex:/^\d{3}-\d{3}-\d{4}$/',
            'routing_number' => 'required|digits:9',
            'account_number' => 'required|numeric',
            'status' => 'required',
            'category' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $payors_type = $request->type;
        if (!empty($request->same_as) && $request->same_as == 'on') {
            $payors_type = 'Both';
        }

        $payor = Payors::find($id);

        $payor->Name = $request->name;
        $payor->UserID = Auth::id();
        $payor->Address1 = $request->address1;
        $payor->Address2 = $request->address2;
        $payor->City = $request->city;
        $payor->State = $request->state;
        $payor->Zip = $request->zip;
        $payor->PhoneNumber = preg_replace('/\D/', '', $request->phone_number);
        $payor->Email = $request->email;
        $payor->BankName = $request->bank_name;
        $payor->RoutingNumber = $request->routing_number;
        $payor->AccountNumber = $request->account_number;
        $payor->Type = $payors_type;
        $payor->Status = $request->status;
        $payor->Category = $request->category;

        $payor->save();


        return redirect()->route('user.' . $type)->with('success', $type . ' updated successfully');
    }

    public function payor_delete($id)
    {
        $type = 'Payors';
        $payor = Payors::find($id);
        $payor?->delete();

        return redirect()->route('user.' . $type)->with('success', $type . ' deleted successfully');
    }

    public function payee_create()
    {
        $type = 'Payee';
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }
        return view('user.' . $type . '.new');
    }

    public function payee_store(Request $request)
    {
        $type = 'Payee';
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $payors_type = $request->type;
        // if(!empty($request->same_as) && $request->same_as == 'on') {
        //     $payors_type = 'Both';
        // }

        $payor = new Payors();

        $payor->Name = $request->name;
        $payor->UserID = Auth::id();
        $payor->Address1 = $request->address1;
        $payor->Address2 = $request->address2;
        $payor->City = $request->city;
        $payor->State = $request->state;
        $payor->Zip = $request->zip;
        $payor->Email = $request->email;
        $payor->BankName = $request->bank_name;
        $payor->RoutingNumber = $request->routing_number;
        $payor->AccountNumber = $request->account_number;
        $payor->Status = $request->status;
        $payor->Type = $payors_type;
        $payor->Category = $request->category;

        $payor->save();


        return redirect()->route('user.' . $type)->with('success', $type . ' added successfully');
    }

    public function payee_edit($id)
    {
        $type = 'Payee';
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }
        $payor = Payors::find($id);
        return view('user.' . $type . '.edit', compact('payor'));
    }

    public function payee_update(Request $request, $id)
    {
        $type = 'Payee';
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $payors_type = $request->type;
        if (!empty($request->same_as) && $request->same_as == 'on') {
            $payors_type = 'Both';
        }

        $payor = Payors::find($id);

        $payor->Name = $request->name;
        $payor->UserID = Auth::id();
        $payor->Address1 = $request->address1;
        $payor->Address2 = $request->address2;
        $payor->City = $request->city;
        $payor->State = $request->state;
        $payor->Zip = $request->zip;
        $payor->Email = $request->email;
        $payor->BankName = $request->bank_name;
        $payor->RoutingNumber = $request->routing_number;
        $payor->AccountNumber = $request->account_number;
        $payor->Type = $payors_type;
        $payor->Status = $request->status;
        $payor->Category = $request->category;

        $payor->save();


        return redirect()->route('user.' . $type)->with('success', $type . ' updated successfully');
    }

    public function payee_delete($id)
    {
        $type = 'Payee';
        $package = Payors::find($id);
        $package?->delete();

        return redirect()->route('user.' . $type)->with('success', $type . ' deleted successfully');
    }

    public function check_payor_email(Request $request)
    {
        $category = $request->category ?? 'RP';
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                Rule::unique('Entities')
                    ->where(function ($query) use ($category) {
                        return $query->where('category', $category)
                            ->where('Type', 'Payor')
                            ->where('UserID', Auth::id());
                    })
                    ->ignore($request->id, 'EntityID')
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        return response()->json(['success' => true]);
    }

    public function add_payor(Request $request)
    {
        $category = $request->category;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('Entities')
                    ->where(function ($query) use ($request, $category) {
                        return $query->where('category', $category)
                            ->where('Type', 'Payor')
                            ->where('UserID', Auth::id());
                    })
                    ->ignore($request->id, 'EntityID')
            ],
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'bank_name' => 'required',
            'routing_number' => 'required|digits:9',
            'account_number' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // Create a new Payee entry (optional)
        if (!empty($request->id)) {
            $payor = Payors::find($request->id);
        } else {
            $payor = new Payors();
        }
        $payor->Name = $request->name;
        $payor->UserID = Auth::id();
        $payor->Address1 = $request->address1;
        $payor->Address2 = $request->address2;
        $payor->City = $request->city;
        $payor->State = $request->state;
        $payor->Zip = $request->zip;
        $payor->Email = $request->email;
        $payor->BankName = $request->bank_name;
        $payor->RoutingNumber = $request->routing_number;
        $payor->AccountNumber = $request->account_number;
        $payor->Status = 'Active';
        $payor->Type = $request->type;
        $payor->Category = $request->category;

        $payor->save();

        // Return success message
        return response()->json(['success' => true, 'payor' => $payor]);
    }

    public function add_payee(Request $request)
    {
        $rules = [
            'email' => [
                'nullable',
                'email',
            ],
            'name' => ['required'],
        ];
        $category = $request->category;

        if (empty($request->email)) {
            $rules['name'][] = Rule::unique('Entities', 'Name')->where(function ($query) use ($category) {
                $query->where('Type', 'Payee')->where('UserID', Auth::id())->where('Category', $category);
            })->ignore($request->id, 'EntityID');
        } else {
            $rules['email'][] = Rule::unique('Entities', 'Email')->where(function ($query) use ($category) {
                $query->where('Type', 'Payee')->where('UserID', Auth::id())->where('Category', $category);
            })->ignore($request->id, 'EntityID');
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // Create a new Payee entry (optional)
        if (!empty($request->id)) {
            $payor = Payors::find($request->id);
        } else {
            $payor = new Payors();
        }
        $payor->Name = $request->name;
        $payor->UserID = Auth::id();
        $payor->Email = $request->email;
        $payor->Status = 'Active';
        $payor->Type = $request->type;
        $payor->Category = $request->category;

        $payor->save();

        // Return success message
        return response()->json(['success' => true, 'payee' => $payor]);
    }

}
