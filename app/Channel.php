<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Channel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'archived',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('channels');
        });

        static::addGlobalScope('alphabetized', function (Builder $builder) {
            $builder->orderBy('name');
        });

        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('archived', false);
        });
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'archived' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the threads for the channel.
     */
    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    /**
     * Archive the channel.
     *
     * @return void
     */
    public function archive(): void
    {
        $this->update(['archived' => true]);
    }

    /**
     * Unarchive the channel.
     *
     * @return void
     */
    public function unarchive(): void
    {
        $this->update(['archived' => false]);
    }

    /**
     * Set the name of the channel.
     *
     * @param  string  $value
     * @return void
     */
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }
}
