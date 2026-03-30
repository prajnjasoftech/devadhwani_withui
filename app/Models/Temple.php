<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class Temple extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'temples';

    protected $fillable = [
        'temple_name',
        'temple_address',
        'temple_logo',
        'phone',
        'database_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    // Note: temple_logo_base64 is NOT auto-appended for performance.
    // Use $temple->append('temple_logo_base64') when the logo is needed.
    protected $appends = [];

    public function getTempleLogoBase64Attribute()
    {
        if (! $this->temple_logo) {
            return null;
        }

        // Logo is stored at storage/app/public/temple_logos/
        $path = 'public/'.$this->temple_logo;

        if (! Storage::exists($path)) {
            return null;
        }

        $file = Storage::get($path);

        return base64_encode($file);
    }
}
