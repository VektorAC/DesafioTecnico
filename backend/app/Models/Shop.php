<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Shop extends Model
{
    protected $fillable = [
        'user_id','provider','domain','credentials_encrypted','scopes','status','meta'
    ];
    protected $casts = ['meta' => 'array'];

    public function setCredentials(array $creds): void {
        $this->attributes['credentials_encrypted'] = Crypt::encryptString(json_encode($creds));
    }

    public function getCredentials(): array {
        return json_decode(Crypt::decryptString($this->credentials_encrypted), true) ?? [];
    }
}
