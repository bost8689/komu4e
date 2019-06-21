<?php

namespace Komu4e\Model\Komuche_ndm;

use Illuminate\Database\Eloquent\Model;

class Photospostmessage extends Model
{
    protected $table = 'photospostmessage';
    protected $fillable = array('filenamemax', 'pathmax', 'photomax_url','filenamemin','pathmin','photomin_url','postmessage_id');
    public $timestamps = FALSE;

    public function postmessages()
    {
        return $this->belongsTo('Komu4e\Model\Komuche_ndm\Postmessage');
    }

    // $Photosvk = Photosvk::create(['filenamemax'=>$filenamemax,'pathmax'=>$pathmax,'photomax_url'=>$photomax_url,'filenamemin'=>$filenamemin,'pathmin'=>$pathmin,'photomin_url'=>$photomin_url,'postmessage_id'=>$postmessage_id,'status'=>$status,'type_status'=>'']);

/*    Static function add_photosvk($filenamemax=Null,$pathmax=Null,$typemax=Null,$photomax_url=Null,$filenamemin=Null,$pathmin=Null,$typemin=Null,$photomin_url=Null,$postmessage_id=1){
        $photosvk = new Photosvk;
        $photosvk-> filenamemax = $filenamemax;
        $photosvk-> pathmax = $pathmax;
        // $photosvk-> typemax = $typemax;
        $photosvk-> photomax_url = $photomax_url;
        $photosvk-> filenamemin = $filenamemin;
        $photosvk-> pathmin = $pathmin;
        // $photosvk-> typemin = $typemin;
        $photosvk-> photomin_url = $photomin_url;        
        $photosvk-> postmessage_id = $postmessage_id;
        $photosvk-> save();
        return($photosvk);
    }*/
}
