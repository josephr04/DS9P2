<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model {
    protected $connection = 'mysql';
    protected $table = 'usuarios';
    protected $primaryKey = 'idUsuario';
    protected $fillable = ['rolUsuario', 'nombreUsuario', 'contrasen', 'correo'];
    // protected $hidden = ['contrasen'];
    public $timestamps = false;
}