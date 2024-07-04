<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $teamQuery = Team::query();

        // Get single data
        if ($id) {
            $team = $teamQuery->find($id);

            if ($team) {
                return ResponseFormatter::success($team, 'Team found');
            }

            return ResponseFormatter::error('Team not found', 404);
        }

        // Get multiple data
        $teams = $teamQuery->where('company_id', $request->company_id);

        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams found'
        );
    }

    public function create(CreateTeamRequest $request)
    {
        try {
            //Upload logo
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            //create company
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id,
            ]);

            if (!$team) {
                throw new Exception('Team not created');
            }

            return ResponseFormatter::success($team, 'Team Created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {

            $team = Team::find($id);

            if (!$team) {
                throw new Exception('team not found');
            }

            //Upload logo
            if ($request->hasFile('icon')) {

                //jika sudah ada logo sebelumnya maka akan terhapus
                if ($team->logo) {
                    Storage::delete($team->icon);
                }

                $path = $request->file('icon')->store('public/icons');
            }

            //create team
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($team, 'Team Updated Successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Check if team exists
            $team = Team::find($id);

            // Check if team owned by user
            if (auth()->user()->company_id != $team->company_id) {
                throw new Exception('You do not have permission to delete this team');
            }

            if (!$team) {
                throw new Exception('Team not found');
            }


            // Delete team's icon from storage
            if ($team->icon) {
                Storage::delete($team->icon);
            }

            // Delete the team
            $team->delete();

            return ResponseFormatter::success($team, 'Team deleted successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
