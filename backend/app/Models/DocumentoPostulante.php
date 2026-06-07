<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DocumentoPostulante extends Model {
    protected $connection = 'mysql_documentos';
    protected $table = 'documento_postulante';
    protected $primaryKey = 'idDocumentoPostulante';
    protected $fillable = [
        'idGradoEst', 'idPostulante', 'codigo_provincia', 'titulo',
        'institucion', 'otraInstitucionn', 'nombreOtraInstitucion',
        'fechaInicio', 'fechaFinaizacion', 'fechaEmision', 'totalHoras'
    ];
    public $timestamps = false;
}