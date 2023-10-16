<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


/**
 * @property int $id
 * @property int $user_id
 * @property string $team
 * @property string $sub_theme
 * @property string $members_data
 * @property string $institution_data
 * @property string $documents_data
 */
class Profile extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    const CRYSTAL_REGISTRATION_DOCUMENT = 'CRYSTAL_REGISTRATION_DOCUMENT';

    const ISOTERM_ABSTRACT_1_DOCUMENT = 'ISOTERM_ABSTRACT_1_DOCUMENT';

    const ISOTERM_WORK_1_DOCUMENT = 'ISOTERM_WORK_1_DOCUMENT';

    const ISOTERM_ABSTRACT_2_DOCUMENT = 'ISOTERM_ABSTRACT_2_DOCUMENT';

    const ISOTERM_WORK_2_DOCUMENT = 'ISOTERM_WORK_2_DOCUMENT';

    const ISOTERM_UNIFIED_DOCUMENT = 'ISOTERM_UNIFIED_DOCUMENT';


    const PAYMENT_DOCUMENT = 'PAYMENT_DOCUMENT';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'team',
        'sub_theme',
        'members_data',
        'institution_data',
        'documents_data',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMembersData(): mixed {
        $membersData = json_decode($this->members_data);

        $membersDataResp = null;
        if(isset($membersData)) {
            // sort by id
            usort($membersData, function($a, $b){
                return strcmp($a->id, $b->id);
            });
            $membersDataResp = $membersData;
        }

        return $membersDataResp;
    }

    public function getInstitutionData(): mixed {
        return json_decode($this->institution_data);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::CRYSTAL_REGISTRATION_DOCUMENT)->singleFile();

        $this->addMediaCollection(self::ISOTERM_ABSTRACT_1_DOCUMENT)->singleFile();
        $this->addMediaCollection(self::ISOTERM_WORK_1_DOCUMENT)->singleFile();
        $this->addMediaCollection(self::ISOTERM_ABSTRACT_2_DOCUMENT)->singleFile();
        $this->addMediaCollection(self::ISOTERM_WORK_2_DOCUMENT)->singleFile();

        $this->addMediaCollection(self::ISOTERM_UNIFIED_DOCUMENT)->singleFile();

        $this->addMediaCollection(self::PAYMENT_DOCUMENT)->singleFile();
    }
}
