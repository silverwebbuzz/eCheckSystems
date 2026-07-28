<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
  use HasFactory, Notifiable;

  protected $table = 'User';

  protected $primaryKey = 'UserID';

  public $timestamps = false;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'FirstName',
    'LastName',
    'Username',
    'Email',
    'PasswordHash',
    'PhoneNumber',
    'CurrentPackageID',
    'Status',
    'CreatedAt',
    'UpdatedAt',
    'reset_token',
    'reset_token_expiry',
    'CusID',
    'Address',
    'CompanyName',
    'City',
    'State',
    'Zip',
    'SubID',
    'timezone',
    'EmailVerified',
    'ip_address'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'PasswordHash',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  static function user_timezone($datetime, $format = 'm/d/Y')
  {
      if (!$datetime) return null;

      $timezone = auth()->check() ? auth()->user()->timezone : 'America/Chicago';

      if (!$datetime instanceof Carbon) {
          $datetime = Carbon::parse($datetime);
      }

      return $datetime->timezone($timezone)->format($format);
  }
}