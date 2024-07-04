<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Models\Responsibility;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Http\Requests\CreateResponsinbiltyRequest;
use App\Http\Requests\UpdateResponsinbiltyRequest;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $responsibilityQuery = Responsibility::query();

        // Get single data
        if ($id) {
            $responsibility = $responsibilityQuery->find($id);

            if ($responsibility) {
                return ResponseFormatter::success($responsibility, 'Responsibility found');
            }

            return ResponseFormatter::error('Responsibility not found', 404);
        }

        // Get multiple data
        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        if ($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $responsibilities->paginate($limit),
            'Responsibilitys found'
        );
    }

    public function create(CreateResponsibilityRequest $request)
    {
        try {
            //create responsibility
            $responsibility = responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            if (!$responsibility) {
                throw new Exception('Responsibility not created');
            }

            return ResponseFormatter::success($responsibility, 'Responsibility Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
    public function destroy($id)
    {
        try {
            // Check if responsibility exists
            $responsiibilities = Responsibility::find($id);

            if (!$responsiibilities) {
                throw new Exception('responsibility not found');
            }

            // Delete the responsibility
            $responsiibilities->delete();

            return ResponseFormatter::success($responsiibilities, 'Responsibility deleted successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
