<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @property int $id
 * @property int $user_id
 * @property string $members_data
 * @property string $institution_data
 * @property string $documents_data
 */
class Profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
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
        return json_decode($this->members_data);
    }

    public function getInstitutionData(): mixed {
        return json_decode($this->institution_data);
    }

    public function getDocumentsData(): mixed {
        return json_decode($this->documents_data);
    }
}
