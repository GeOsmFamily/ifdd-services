<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * App\Models\Osc
 *
 * @property int $id
 * @property string $name
 * @property string $abbreviation
 * @property string $numero_osc
 * @property string $pays
 * @property string $date_fondation
 * @property string|null $description
 * @property string $personne_contact
 * @property string $telephone
 * @property string $email_osc
 * @property string|null $site_web
 * @property string|null $facebook
 * @property string|null $twitter
 * @property string|null $instagram
 * @property string|null $linkedin
 * @property string $longitude
 * @property string $latitude
 * @property string $siege
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Osc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Osc newQuery()
 * @method static \Illuminate\Database\Query\Builder|Osc onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Osc query()
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereDateFondation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereEmailOsc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereFacebook($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereInstagram($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereLinkedin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereNumeroOsc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc wherePays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc wherePersonneContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereSiege($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereSiteWeb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereTwitter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Osc withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Osc withoutTrashed()
 * @mixin \Eloquent
 * @property int $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategorieOdd[] $categorieOdds
 * @property-read int|null $categorie_odds_count
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereUserId($value)
 * @property bool $active
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Osc whereActive($value)
 */
class Osc extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'user_id',
        'name',
        'abbreviation',
        'pays',
        'date_fondation',
        'description',
        'personne_contact',
        'telephone',
        'email_osc',
        'site_web',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'longitude',
        'latitude',
        'siege', 'reference'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function categorieOdds()
    {
        return $this->belongsToMany(CategorieOdd::class, "osc_categorie_odds", "osc_id", "categorie_odd_id")->withPivot('description');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function zoneInterventions()
    {
        return $this->hasMany(ZoneIntervention::class, 'osc_id');
    }

    public function oscCategorieOdds()
    {
        return $this->hasMany(OscCategorieOdd::class, 'osc_id');
    }
}
