<?php

namespace Komu4e\Model\Komuche_ndm;

use Illuminate\Database\Eloquent\Model;

class Photosbnip extends Model
{
    protected $table = 'photosbnip';
    protected $fillable = array('filenamemax','pathmax','bnip_id');
    public $timestamps = FALSE;

    public function bnips(){
        return $this->belongsTo('Komu4e\Model\Bnip\Komuche_ndm');
    }

    // $Photosbnip = Photosbnip::create(['filenamemax'=>$filenamemax,'pathmax'=>$pathmax,'bnip_id'=>$bnip_id]);

}
