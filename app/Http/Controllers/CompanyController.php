<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use Carbon\Carbon;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $companies = Company::where('UserID', Auth::id())->get();

            return datatables()->of($companies)
                ->addIndexColumn()
                ->addColumn('logo', function ($row) {
                    if(!empty($row->Logo)) {
                        return '<img src="' . asset($row->Logo) . '" alt="Company Logo" style="width: 50px;">';
                    } else {
                        return '<img src="' . asset('assets/img/empty.jpg') . '" alt="Company Logo" style="width: 50px;">';
                    }
                })
                ->addColumn('CreatedAt', function ($row) {
                    return Carbon::parse($row->CreatedAt)->format('m/d/Y'); 
                })
                ->addColumn('UpdatedAt', function ($row) {
                    return Carbon::parse($row->UpdatedAt)->format('m/d/Y');
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge ' .
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">' . $row->Status . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('user.company.edit', ['id' => $row->CompanyID]);
                    $deleteUrl = route('user.company.delete', ['id' => $row->CompanyID]);

                    return '<div class="d-flex">
                                <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i>
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                data-bs-target="#delete' . $row->CompanyID . '">
                                    <i class="ti ti-trash me-1"></i>
                                </a>
                            </div>
                            <div class="modal fade" id="delete' . $row->CompanyID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Company</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this company?</p>
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
                ->rawColumns(['logo','CreatedAt', 'UpdatedAt', 'status', 'actions'])
                ->make(true);
        }

        return view('user.company.index');
    }

    public function create() 
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        return view('user.company.new');   
    }

    public function store(Request $request) 
    {  
        $validator = Validator::make($request->all(), [
            'name' => 'required' ,
            'email' => 'nullable|email',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'bank_name' => 'required',
            'routing_number' => 'required|digits:9',
            'account_number' => 'required|numeric',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $uniqueName = Str::uuid() . '.' . $file->getClientOriginalExtension(); // Generate a unique name with extension
            $destinationPath = public_path('logos'); // Path to "public/logos"

            // Ensure the directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Move the uploaded file to the public path
            $file->move($destinationPath, $uniqueName);
            
            // Save the path relative to public
            $logoPath = 'logos/' . $uniqueName;
        }


        
        $company = new Company();

        $slug = Str::slug($request->name, '-');
        $originalSlug = $slug;

        // Check for uniqueness and append a counter if necessary
        $counter = 1;
        while (Company::where('Slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $company->Slug = $slug;

        $company->Name = $request->name;
        $company->UserID = Auth::id();
        $company->Address1 = $request->address1;
        $company->Address2 = $request->address2;
        $company->City = $request->city;
        $company->State = $request->state;
        $company->Zip = $request->zip;
        $company->Email = $request->email;
        $company->Logo = $logoPath; // Save the path of the logo
        $company->BankName = $request->bank_name;
        $company->RoutingNumber = $request->routing_number;
        $company->AccountNumber = $request->account_number;
        $company->PageURL = $request->page_url;
        $company->PageDescription = $request->page_description;
        $company->Slug = $slug;
        $company->Status = $request->status;

        $company->save();


        return redirect()->route('user.company')->with('success', 'Company added successfully');
    }

    public function edit($id)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        $company = Company::find($id);
        return view('user.company.edit', compact('company'));
    }

    public function update(Request $request, $id) 
    {  
        $validator = Validator::make($request->all(), [
            'name' => 'required' ,
            'email' => 'nullable|email',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'bank_name' => 'required',
            'routing_number' => 'required|digits:9',
            'account_number' => 'required|numeric',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $slug = Str::slug($request->name, '-');
        
        $company = Company::find($id);

        
        $logoPath =  $company->Logo;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $uniqueName = Str::uuid() . '.' . $file->getClientOriginalExtension(); // Generate a unique name with extension
            $destinationPath = public_path('logos'); // Path to "public/logos"
        
            // Ensure the directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
        
            // Move the uploaded file to the public path
            $file->move($destinationPath, $uniqueName);
            
            // Save the path relative to public
            $logoPath = 'logos/' . $uniqueName;
        }
        

        $company->Name = $request->name;
        $company->UserID = Auth::id();
        $company->Address1 = $request->address1;
        $company->Address2 = $request->address2;
        $company->City = $request->city;
        $company->State = $request->state;
        $company->Zip = $request->zip;
        $company->Email = $request->email;
        $company->Logo = $logoPath; // Save the path of the logo
        $company->BankName = $request->bank_name;
        $company->RoutingNumber = $request->routing_number;
        $company->AccountNumber = $request->account_number;
        $company->PageURL = $request->page_url;
        $company->PageDescription = $request->page_description;
        $company->Slug = $slug;
        $company->Status = $request->status;

        $company->save();


        return redirect()->route('user.company')->with('success', 'Updated added successfully');
    }

    public function delete($id)
    {
        $package = Company::find($id);
        $package->delete();

        return redirect()->route('user.company')->with('success', 'Company deleted successfully');
    }

    public function add_payee(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'name' => 'required' ,
            'email' => 'nullable|email',
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'bank_name' => 'required',
            'routing_number' => 'required',
            'account_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // Create a new Payee entry (optional)
        if(!empty($request->id)) {
            $company = Company::find($request->id);
        } else {
            $company = new Company();
            $slug = Str::slug($request->name, '-');
            $originalSlug = $slug;

            // Check for uniqueness and append a counter if necessary
            $counter = 1;
            while (Company::where('Slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $company->Slug = $slug;
        }
        

        $company->Name = $request->name;
        $company->UserID = Auth::id();
        $company->Address1 = $request->address1;
        $company->Address2 = $request->address2;
        $company->City = $request->city;
        $company->State = $request->state;
        $company->Zip = $request->zip;
        $company->Email = $request->email;
        $company->BankName = $request->bank_name;
        $company->RoutingNumber = $request->routing_number;
        $company->AccountNumber = $request->account_number;
        $company->Status = 'Active';

        $company->save();

        // Return success message
        return response()->json(['success' => true, 'payee' => $company]);
    }

}
