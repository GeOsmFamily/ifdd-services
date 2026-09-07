<?php

namespace App\Http\Controllers\Api;

use App\Models\CategorieOdd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 *
 * @group ODD management
 *
 * APIs for managing ODDs
 */
class CategorieOddController extends BaseController
{

    /**
     * Get all CategorieOdds.
     *
     * @header Content-Type application/json
     * @responseFile storage/responses/getcategorieodds.json
     */
    public function index()
    {
        $categorieOdds = CategorieOdd::all();
        $categorieOdds->each(function ($categorieOdd) {
            $categorieOdd->odd;
        });
        return $this->sendResponse($categorieOdds, 'Liste des CategorieOdds');
    }


    /**
     * Add a new CategorieOdd.
     *
     * @authenticated
     * @header Content-Type application/json
     * @bodyParam category_number string required the number of the categorieOdd. Example:12.1
     * @bodyParam intitule string required the title of the categorieOdd. Example: Faim
     * @bodyParam name_en string required the english name of the category odd. Example: Eat
     * @bodyParam id_odd int required the id of the odd. Example: 1
     * @responseFile storage/responses/addcategorieodd.json
     */
    public function store(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'category_number' => 'required',
            'intitule' => 'required',
            'name_en' => 'required',
            'id_odd' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erreur de paramètres.', $validator->errors(), 400);
        }

        try {
            DB::beginTransaction();


            $categorieOdd = CategorieOdd::create($request->all());
            DB::commit();
            return $this->sendResponse($categorieOdd, 'CategorieOdd créé avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la création de la catégorie.', $e->getMessage(), 400);
        }
    }


    /**
     * Show the specified CategorieOdd.
     * @urlParam id required The ID of the CategorieOdd. Example: 1
     * @responseFile storage/responses/showcategorieodd.json
     */
    public function show($id)
    {
        $categorieOdd = CategorieOdd::find($id);
        $categorieOdd->odd;
        return $this->sendResponse($categorieOdd, 'CategorieOdd retrouvé avec succès');
    }

    /**
     * Update the specified CategorieOdd.
     *
     * @authenticated
     * @header Content-Type application/json
     * @urlParam id required The ID of the CategorieOdd. Example: 1
     * @bodyParam category_number string required the number of the categorieOdd. Example:12.2
     * @bodyParam intitule string required the title of the categorieOdd. Example: Faim
     * @bodyParam name_en string required the english name of the category odd. Example: Eat
     * @bodyParam id_odd int required the id of the odd. Example: 1
     * @responseFile storage/responses/updatecategorieodd.json
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role != 1) {
            return $this->sendError('Erreur.', ['error' => 'Accès réservé aux administrateurs.'], 403);
        }

        $validator =  Validator::make($request->all(), [
            'category_number' => 'required',
            'intitule' => 'required',
            'name_en' => 'required',
            'id_odd' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erreur de paramètres.', $validator->errors(), 400);
        }

        $categorieOdd = CategorieOdd::find($id);
        if (!$categorieOdd) {
            return $this->sendError('Erreur.', ['error' => 'CategorieOdd introuvable.'], 404);
        }

        try {
            DB::beginTransaction();

            $categorieOdd->update($request->all());

            DB::commit();
            return $this->sendResponse($categorieOdd, 'CategorieOdd modifié avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la modification de la catégorie.', $e->getMessage(), 400);
        }
    }


    /**
     * Delete the specified CategorieOdd.
     *
     * @authenticated
     * @urlParam id required The ID of the CategorieOdd. Example: 1
     * @responseFile storage/responses/deletecategorieodd.json
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || $user->role != 1) {
            return $this->sendError('Erreur.', ['error' => 'Accès réservé aux administrateurs.'], 403);
        }

        $categorieOdd = CategorieOdd::find($id);
        if (!$categorieOdd) {
            return $this->sendError('Erreur.', ['error' => 'CategorieOdd introuvable.'], 404);
        }

        try {
            DB::beginTransaction();

            $categorieOdd->delete();

            DB::commit();
            return $this->sendResponse($categorieOdd, 'CategorieOdd supprimé avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la suppression de la catégorie.', $e->getMessage(), 400);
        }
    }
}
