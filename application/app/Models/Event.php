<?php

namespace App\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    // added use HasFactory to allow for seeding db
    use HasFactory;

    /**
     * @var string[] $fillable
     */
    protected $fillable = [
        'uuid',
        'name',
        'policyIds',
        'nonceValidForMinutes',
        'hodlAsset',
        'startDateTime',
        'endDateTime',
        'location',
        'eventStart',
        'eventEnd',
        'eventDate',
        'image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'policyIds' => Json::class,
    ];

    protected $dates = [
        'startDateTime',
        'endDateTime',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'eventId');
    }

    public function description()
    {
        $description = "";
        if ($this->eventDate) {
            $description .= date('l, F jS, Y', strtotime($this->eventDate));
        }

        if ($this->eventStart && $this->eventEnd) {
            $description .= " {$this->eventStart} to {$this->eventEnd}.";
        } elseif ($this->eventStart) {
            $description .= " {$this->eventStart}";
        } elseif ($this->eventEnd) {
            $description .= " until {$this->eventEnd}";
        }

        if ($this->location) {
            $description .= " {$this->location}";
        }

        return $description;
    }
    // Added protected boot function to populate uuid table
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->uuid)) {
                $event->uuid = (string) Str::uuid();
            }
        });
    }

}
