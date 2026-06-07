<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Postulante extends Model {
    protected $connection = 'mysql';
    protected $table = 'postulante';
    protected $primaryKey = 'idPostulante';
    protected $fillable = [
        'idUsuario', 'rangoAcademico', 'nombre', 'nombre2', 'apellido', 'apellido2',
        'prefijo', 'tomo', 'asiento', 'genero', 'estadoCivil', 'usaCasada',
        'apelCasada', 'tipoSangre', 'fechaNacimiento', 'codigo_provincia',
        'codigo_distrito', 'codigo_corregimiento', 'comunidad', 'calle', 'casa',
        'detallesDireccion', 'telefono', 'telefono2', 'celular', 'celular2', 'correoPostulante'
    ];
    public $timestamps = false;
}