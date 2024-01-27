<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });

        // Get single data
        if ($id) {
            $company = $companyQuery->find($id);

            if ($company) {
                return ResponseFormatter::success($company, 'Company found');
            }

            return ResponseFormatter::error('Company not found', 404);
        }

        // Get multiple data
        $companies = $companyQuery;

        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    }

    public function create(CompanyRequest $request)
    {
        try {
            //Upload logo
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            //create company
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path
            ]);

            if (!$company) {
                throw new Exception('Company not created');
            }

            //nempelin company to user
            $user = User::findOrFail(Auth::id());
            $user->companies()->attach($company->id);

            //load user ke company
            $company->load('users');

            return ResponseFormatter::success($company, 'Company Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {

            $company = Company::find($id);

            if (!$company) {
                throw new Exception('Company not found');
            }

            //Upload logo
            if ($request->hasFile('logo')) {

                //jika sudah ada logo sebelumnya maka akan terhapus
                if ($company->logo) {
                    Storage::delete($company->logo);
                }

                $path = $request->file('logo')->store('public/logos');
            }

            //create company
            $company->update([
                'name' => $request->name,
                'logo' => isset($path) ? $path : $company->logo
            ]);

            return ResponseFormatter::success($company, 'Company Updated Successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
