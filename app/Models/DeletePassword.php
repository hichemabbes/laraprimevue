<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class DeletePassword extends Model
{
    protected $table = 'delete_passwords';

    protected $fillable = ['password'];

    protected $hidden = ['password'];

    public function verifyPassword($password)
    {
        return Hash::check($password, $this->password);
    }

    // Réactiver le mutateur pour hacher automatiquement le mot de passe
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
