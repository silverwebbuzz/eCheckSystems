<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use App\Models\PaymentSubscription;

class User extends Authenticatable
{
  use HasFactory, Notifiable;

  const STATUS_ACTIVE = 'Active';
  const STATUS_INACTIVE = 'Inactive';

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
    'ip_address',
    'reason',
    'approved_at',
    'approved_by'
  ];

  public function isActive(): bool
  {
      return $this->Status === self::STATUS_ACTIVE;
  }

  public function isInactive(): bool
  {
      return $this->Status === self::STATUS_INACTIVE;
  }

  public function isFraud(): bool
  {
      return $this->isInactive() && strtolower((string) $this->reason) === 'fraud';
  }

  public function isOnTrialPackage(): bool
  {
      return (string) $this->CurrentPackageID === '-1';
  }

  public function isPendingApproval(): bool
  {
      return $this->isActive() && empty($this->approved_at);
  }

  public function isApproved(): bool
  {
      return !empty($this->approved_at);
  }

  public function needsAccountChoice(): bool
  {
      if (!$this->isApproved()) {
          return false;
      }

      if (!empty($this->CurrentPackageID)) {
          return false;
      }

      return !PaymentSubscription::where('UserID', $this->UserID)->exists();
  }

  public function canLogin(): bool
  {
      return $this->isActive() && $this->isApproved();
  }

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
