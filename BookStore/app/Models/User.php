<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'firstname',
        'lastname',
        'email',
        'phone_no',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    
    public function userEmailValidation($email)
    {
        $user = User::where('email', $email)->first();

        return $user;
    }

    public function saveUserDetails($request)
    {
        $user = User::create([
            'role' => $request->role,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'phone_no' => $request->phone_no,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        return $user;
    }

    public function userVerification($currentUserId)
    {
        $userId = User::select('id')->where([['role', '=', 'user'], ['id', '=', $currentUserId]])->get();
        return $userId;
    }

    public static function checkUser($currentUserId)
    {
        $user = User::where('role', 'user')->where('id', $currentUserId)->first();
        return $user;
    }


    public function books()
    {
        return $this->hasMany('App\Models\Book');
    }

    public function carts()
    {
        return $this->hasMany('App\Models\cart');
    }

    public function addresses()
    {
        return $this->hasMany('App\Models\Address');
    }
    
}
