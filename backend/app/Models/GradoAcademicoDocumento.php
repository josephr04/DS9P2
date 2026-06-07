<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GradoAcademicoDocumento extends Model {
    protected $connection = 'mysql_documentos';
    protected $table = 'gradoacademico_documento';
    protected $primaryKey = 'idGradoEst';
    protected $fillable = ['nombreGradoEst'];
    public $timestamps = false;
}