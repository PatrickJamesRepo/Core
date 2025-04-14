<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    // added use HasFactory to allow for seeding db
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[] $fillable
     */
    protected $fillable = [
        'eventId',
        'policyId',
        'assetId',
        'stakeKey',
        'signatureNonce',
        'ticketNonce',
        'isCheckedIn',
        'signature',
        'checkInTime',
        'checkInUser',
    ];

    protected $dates = [
        'checkInTime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'eventId');
    }
}
