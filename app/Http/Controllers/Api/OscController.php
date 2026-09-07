<?php

namespace App\Http\Controllers\Api;

use App\Models\CategorieOdd;
use App\Models\Osc;
use App\Models\ZoneIntervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 *
 * @group OSC management
 *
 * APIs for managing OSCs
 */
class OscController extends BaseController
{
    /**
     * Get all OSCs.
     *
     * @header Content-Type application/json
     * @responseFile storage/responses/getoscs.json
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page') ?? 50;
        //  $oscs = Osc::paginate($per_page);
        $oscs = Osc::where('active', 1)->paginate($per_page);
        $oscs->setPath(env('APP_URL') . '/api/osc');

        foreach ($oscs as $osc) {
            $osc->user;
            foreach ($osc->categorieOdds as $categorieOdd) {
                $categorieOdd->odd;
            }
            $osc->zoneInterventions;
        }
        return $this->sendResponse($oscs, 'Liste des OSCs');
    }

    /**
     * getActiveOscs.
     *
     * @header Content-Type application/json
     * @responseFile storage/responses/getoscs.json
     */
    public function getActiveOscs(Request $request)
    {
        $per_page = $request->input('per_page') ?? 50;
        $oscs = Osc::where('active', 1)->paginate($per_page);
        $oscs->setPath(env('APP_URL') . '/api/osc');

        foreach ($oscs as $osc) {
            $osc->user;
            foreach ($osc->categorieOdds as $categorieOdd) {
                $categorieOdd->odd;
            }
            $osc->zoneInterventions;
        }
        return $this->sendResponse($oscs, 'Liste des OSCs');
    }


    /**
     * Add a new OSC.
     *
     * @authenticated
     * @header Content-Type application/json
     * @bodyParam name string required the name of the osc. Example: Faim
     * @bodyParam abbreviation string required the abbreviation of the osc. Example: F
     * @bodyParam pays string required the country of the osc. Example: France
     * @bodyParam date_fondation string the date of the osc. Example: 12/12/12
     * @bodyParam description string  the description of the osc. Example: Faim
     * @bodyParam personne_contact string the contact person of the osc. Example: Faim
     * @bodyParam telephone string  the telephone of the osc. Example: 12
     * @bodyParam email_osc string the email of the osc. Example: Faim
     * @bodyParam site_web string  the website of the osc. Example: Faim
     * @bodyParam facebook string  the facebook of the osc. Example: Faim
     * @bodyParam twitter string  the twitter of the osc. Example: Faim
     * @bodyParam instagram string  the instagram of the osc. Example: Faim
     * @bodyParam linkedin string  the linkedin of the osc. Example: Faim
     * @bodyParam longitude string required the longitude of the osc. Example: Faim
     * @bodyParam latitude string required the latitude of the osc. Example: Faim
     * @bodyParam reference string the reference of the osc. Example: OMS
     * @bodyParam siege string required the siege of the osc. Example: Faim
     * @bodyParam zone_intervention required the zone of the osc. Example: [ {"name":"Zone","longitude":"13","latitude":"7"},{"name":"Zone2","longitude":"13","latitude":"7"},{"name":"Zone3","longitude":"13","latitude":"7"}]
     * @bodyParam osccategoriesOdd required the categories of the osc. Example: [{"id" : 12,"description":"Une Osc"},{"id" : 20,"description":"Une Osc1"}]
     * @responseFile storage/responses/addosc.json
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'abbreviation' => 'required',
            'pays' => 'required',
            'date_fondation' => '',
            'description' => '',
            'personne_contact' => '',
            'telephone' => '',
            'email_osc' => '',
            'site_web' => '',
            'facebook' => '',
            'twitter' => '',
            'instagram' => '',
            'linkedin' => '',
            'longitude' => 'required',
            'latitude' => 'required',
            'reference' => '',
            'siege' => 'required',
            'zone_intervention' => 'required',
            'osccategoriesOdd' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        try {
            DB::beginTransaction();

            $osc =  $user->oscs()->create($input);

            foreach ($input['osccategoriesOdd'] as $categorieOdd) {
                $osc->categorieOdds()->attach($categorieOdd['id'], ['description' => $categorieOdd['description']]);
            }

            foreach ($request->zone_intervention as $zone) {
                ZoneIntervention::create([
                    'osc_id' => $osc->id,
                    'name' => $zone['name'],
                    'longitude' => $zone['longitude'],
                    'latitude' => $zone['latitude'],
                ]);
            }

            DB::commit();

            return $this->sendResponse($osc, 'OSC created successfully.', 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->sendError('Error', $th->getMessage(), 400);
        }
    }

    /**
     * Get a single OSC.
     *
     * @urlParam id required The ID of the OSC.
     * @responseFile storage/responses/getosc.json
     */
    public function show($id)
    {
        $osc = Osc::find($id);
        $osc->user;
        foreach ($osc->categorieOdds as $categorieOdd) {
            $categorieOdd->odd;
        }
        $osc->zoneInterventions;
        return $this->sendResponse($osc, 'OSC retrieved successfully.');
    }

    /**
     * Update a OSC.
     *
     * @urlParam id required The ID of the OSC.
     * @authenticated
     * @header Content-Type application/json
     * @bodyParam name string required the name of the osc. Example: Faim
     * @bodyParam abbreviation string required the abbreviation of the osc. Example: F
     * @bodyParam pays string required the country of the osc. Example: France
     * @bodyParam date_fondation string required the date of the osc. Example: 12/12/12
     * @bodyParam description string  the description of the osc. Example: Faim
     * @bodyParam personne_contact string required the contact person of the osc. Example: Faim
     * @bodyParam telephone string required the telephone of the osc. Example: 12
     * @bodyParam email_osc string required the email of the osc. Example: Faim
     * @bodyParam site_web string  the website of the osc. Example: Faim
     * @bodyParam facebook string  the facebook of the osc. Example: Faim
     * @bodyParam twitter string  the twitter of the osc. Example: Faim
     * @bodyParam instagram string  the instagram of the osc. Example: Faim
     * @bodyParam linkedin string  the linkedin of the osc. Example: Faim
     * @bodyParam longitude string required the longitude of the osc. Example: Faim
     * @bodyParam latitude string required the latitude of the osc. Example: Faim
     * @bodyParam reference string the reference of the osc. Example: OMS
     * @bodyParam siege string required the siege of the osc. Example: Faim
     * @bodyParam zone_intervention required the zone of the osc. Example: [ {"name":"Zone","longitude":"13","latitude":"7"},{"name":"Zone2","longitude":"13","latitude":"7"},{"name":"Zone3","longitude":"13","latitude":"7"}]
     * @bodyParam osccategoriesOdd required the categories of the osc. Example: [{"id" : 12,"description":"Une Osc"},{"id" : 20,"description":"Une Osc1"}]
     * @responseFile storage/responses/updateosc.json
     */
    public function update(Request $request, $id)
    {
        $osc = Osc::find($id);
        if (!$osc) {
            return $this->sendError('Erreur', ['error' => 'OSC introuvable.'], 404);
        }

        $user = Auth::user();
        if (!$user || ($user->role != 1 && $user->id != $osc->user_id)) {
            return $this->sendError('Erreur', ['error' => "Vous n'êtes pas autorisé à modifier cette OSC."], 403);
        }

        $input = $request->all();


        try {
            DB::beginTransaction();


            $osc->name = $request->name ?? $osc->name;
            $osc->abbreviation = $request->abbreviation ?? $osc->abbreviation;
            $osc->pays = $request->pays ?? $osc->pays;
            $osc->date_fondation = $request->date_fondation ?? $osc->date_fondation;
            $osc->description = $request->description ?? $osc->description;
            $osc->personne_contact = $request->personne_contact ?? $osc->personne_contact;
            $osc->telephone = $request->telephone ?? $osc->telephone;
            $osc->email_osc = $request->email_osc ?? $osc->email_osc;
            $osc->site_web = $request->site_web ?? $osc->site_web;
            $osc->facebook = $request->facebook ?? $osc->facebook;
            $osc->twitter = $request->twitter ?? $osc->twitter;
            $osc->instagram = $request->instagram ?? $osc->instagram;
            $osc->linkedin = $request->linkedin ?? $osc->linkedin;
            $osc->longitude = $request->longitude ?? $osc->longitude;
            $osc->latitude = $request->latitude ?? $osc->latitude;
            $osc->reference = $request->reference ?? $osc->reference;
            $osc->siege = $request->siege ?? $osc->siege;

            if ($request->active) {
                $osc->active = $request->active;
            }

            $osc->save();

            if ($request->osccategoriesOdd) {
                $osc->categorieOdds()->detach();

                foreach ($input['osccategoriesOdd'] as $categorieOdd) {
                    $osc->categorieOdds()->attach($categorieOdd['id'], ['description' => $categorieOdd['description']]);
                }
            }

            if ($request->zone_intervention) {

                foreach ($request->zone_intervention as $zone) {
                    $zoneIntervention = ZoneIntervention::where('osc_id', $osc->id)->where('id', $zone['id'])->first();
                    if ($zoneIntervention) {
                        $zoneIntervention->name = $zone['name'];
                        $zoneIntervention->longitude = $zone['longitude'];
                        $zoneIntervention->latitude = $zone['latitude'];
                        $zoneIntervention->save();
                    } else {
                        ZoneIntervention::create([
                            'osc_id' => $osc->id,
                            'name' => $zone['name'],
                            'longitude' => $zone['longitude'],
                            'latitude' => $zone['latitude'],
                        ]);
                    }
                }
            }




            DB::commit();

            return $this->sendResponse($osc, 'OSC updated successfully.', 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->sendError('Error', $th->getMessage(), 400);
        }
    }

    /**
     * Delete a OSC.
     *
     * @urlParam id required The ID of the OSC.
     * @authenticated
     * @responseFile storage/responses/deleteosc.json
     */
    public function destroy($id)
    {
        $osc = Osc::find($id);
        if (!$osc) {
            return $this->sendError('Erreur', ['error' => 'OSC introuvable.'], 404);
        }

        $user = Auth::user();
        if (!$user || ($user->role != 1 && $user->id != $osc->user_id)) {
            return $this->sendError('Erreur', ['error' => "Vous n'êtes pas autorisé à supprimer cette OSC."], 403);
        }

        try {
            DB::beginTransaction();

            $osc->categorieOdds()->detach();

            $osc->zoneInterventions()->delete();

            $osc->delete();

            DB::commit();

            return $this->sendResponse($osc, 'OSC deleted successfully.', 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->sendError('Error', $th->getMessage(), 400);
        }
    }


    /**
     * Search OSCs by idsCategorieOdd.
     * @bodyParam idsCategorieOdd required The ids of the categories of the OSC. Example: 12,20
     * @responseFile storage/responses/searchosc.json
     */
    public function searchOsc(Request $request)
{
    $idsCategorieOdd = explode(',', $request->idsCategorieOdd);

    $data = array();

    foreach ($idsCategorieOdd as $iValue) {
        $categorieOdd = CategorieOdd::find($iValue);
        $categorieOdd->oscs;

        foreach ($categorieOdd->oscs as $osc) {
            // Ajoutez une condition pour vérifier si l'OSC est active
            if ($osc->active == 1) {
                $osc->user;

                foreach ($osc->categorieOdds as $categorieOdd) {
                    $categorieOdd->odd;
                }

                $osc->zoneInterventions;

                $bool = $this->checkIfOscInDataArray($data, $osc);

                if (!$bool) {
                    $data[] = $osc;
                }
            }
        }
    }

    return $this->sendResponse($data, 'Active OSC retrieved successfully.');
}





    /**
     * Search OSCs.
     *
     * @header Content-Type application/json
     * @urlParam q string required the query search. Example: ONG
     * @responseFile storage/responses/getoscs.json
     */
 public function searchOscByQuery(Request $request)
    {
        $q  = $request->input('q');
        $oscs = OSC::search($q)->get();

        foreach ($oscs as $osc) {
            $osc->user;
            foreach ($osc->categorieOdds as $categorieOdd) {
                $categorieOdd->odd;
            }
            $osc->zoneInterventions;
        }

        return $this->sendResponse($oscs, 'OSC retrieved successfully.');
    }


    /**
     * Count OSCs.
     *
     * @header Content-Type application/json
     * @responseFile storage/responses/countosc.json
     */
    public function countOscInDb()
    {
        $oscs = Osc::where('active', 1)->get();
        $count = count($oscs);
        return $this->sendResponse($count, 'number of OSCs in db');
    }
}
