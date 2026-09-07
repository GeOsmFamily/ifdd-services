<?php

namespace App\Http\Controllers\Api;

use App\Models\ZoneIntervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 *
 * @group ZoneIntervention management
 *
 * APIs for managing ZoneInterventions
 */
class ZoneInterventionController extends BaseController
{

    /**
     * Get all ZoneInterventions.
     *
     * @header Content-Type application/json
     * @responseFile storage/responses/getzoneinterventions.json
     */
    public function index()
    {
        $zoneInterventions = ZoneIntervention::all();


        return $this->sendResponse($zoneInterventions, 'Liste des ZoneInterventions');
    }


    /**
     * Add a new ZoneIntervention.
     * @authenticated
     *
     * @bodyParam osc_id int required The osc id.
     * @bodyParam name string required The name of zoneIntervention. Example: Faim
     * @bodyParam longitude string required The longitude of zoneIntervention. Example: 13
     * @bodyParam latitude string required The latitude of zoneIntervention. Example: 2
     * @responseFile storage/responses/addzoneintervention.json
     *
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'osc_id' => 'required',
            'name' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {

            DB::beginTransaction();

            $zoneIntervention = ZoneIntervention::create($input);

            DB::commit();

            return $this->sendResponse($zoneIntervention, 'ZoneIntervention created successfully.', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Error', $e->getMessage(), 400);
        }
    }



    /**
     * Get a single ZoneIntervention.
     *
     * @urlParam id required The ID of the ZoneIntervention.
     * @responseFile storage/responses/getzoneintervention.json
     */

    public function show($id)
    {
        $zoneIntervention = ZoneIntervention::find($id);
        $zoneIntervention->oscs;

        return $this->sendResponse($zoneIntervention, 'ZoneIntervention retrieved successfully.');
    }

    /**
     * Update a ZoneIntervention.
     * @authenticated
     *
     * @bodyParam osc_id int required The osc id.
     * @bodyParam name string required The name of zoneIntervention. Example: Faim
     * @bodyParam longitude string required The longitude of zoneIntervention. Example: 13
     * @bodyParam latitude string required The latitude of zoneIntervention. Example: 2
     * @responseFile storage/responses/updatezoneintervention.json
     *
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role != 1) {
            return $this->sendError('Erreur.', ['error' => 'Accès réservé aux administrateurs.'], 403);
        }

        $zoneIntervention = ZoneIntervention::find($id);
        if (!$zoneIntervention) {
            return $this->sendError('Erreur.', ['error' => 'ZoneIntervention introuvable.'], 404);
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'osc_id' => 'required',
            'name' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 400);
        }

        try {

            DB::beginTransaction();

            $zoneIntervention->name = $input['name'] ?? $zoneIntervention->name;
            $zoneIntervention->longitude = $input['longitude'] ?? $zoneIntervention->longitude;
            $zoneIntervention->latitude = $input['latitude'] ?? $zoneIntervention->latitude;
            $zoneIntervention->save();

            DB::commit();

            return $this->sendResponse($zoneIntervention, 'ZoneIntervention updated successfully.', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Error', $e->getMessage(), 200);
        }
    }


    /**
     * Delete a ZoneIntervention.
     * @authenticated
     *
     * @urlParam id required The ID of the ZoneIntervention.
     * @responseFile storage/responses/deletezoneintervention.json
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || $user->role != 1) {
            return $this->sendError('Erreur.', ['error' => 'Accès réservé aux administrateurs.'], 403);
        }

        $zoneIntervention = ZoneIntervention::find($id);
        if (!$zoneIntervention) {
            return $this->sendError('Erreur.', ['error' => 'ZoneIntervention introuvable.'], 404);
        }

        try {

            DB::beginTransaction();

            $zoneIntervention->delete();

            DB::commit();

            return $this->sendResponse($zoneIntervention, 'ZoneIntervention deleted successfully.', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
