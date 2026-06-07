<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RutaDocumento extends Model {
    protected $connection = 'mysql_documentos';
    protected $table = 'ruta_documento';
    protected $primaryKey = 'idRutadoc';
    protected $fillable = ['idDocumentoPostulante', 'ruta'];
    public $timestamps = false;
}