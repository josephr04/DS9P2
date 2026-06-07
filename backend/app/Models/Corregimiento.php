<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Corregimiento extends Model {
    protected $connection = 'mysql';
    protected $table = 'corregimiento';
    protected $primaryKey = 'codigo_corregimiento';
    protected $fillable = ['codigo_provincia', 'codigo_distrito', 'codigo', 'codigo_corregimiento', 'nombre_corregimiento'];
    public $timestamps = false;
}