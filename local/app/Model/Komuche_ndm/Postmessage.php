<?php

namespace Komu4e\Model\Komuche_ndm;

use Illuminate\Database\Eloquent\Model;

class Postmessage extends Model
{
    protected $table = 'postmessage';
    protected $fillable = array('source_id','usersvk_id','text','date','user_id','status','type_status');

    public function photospostmessage(){
    return $this->hasMany('Komu4e\Model\Komuche_ndm\Photospostmessage');
    }
    public function usersvk()
    {
        return $this->belongsTo ('Komu4e\Model\Komuche_ndm\Usersvk');
    }
    public function user(){
        return $this->belongsTo('Komu4e\User');
    }

    // protected $fillable = array('source_id', 'type_source', 'name_source','usersvk_id','text','comments','like','repost','date','user_id','status','type_status');

    // $Postmessage = Postmessage::create(['source_id'=>$post_id,'type_source'=>$type_source,'name_source'=>$group_id,'usersvk_id'=>$usersvk_id,'text'=>$text,'comments'=>Null,'like'=>Null,'repost'=>Null,'date'=>$date_post,'user_id'=>Auth::id(),'status'=>Null,'type_status'=>Null]);

    // Static function add_postmessage($source_id,$type_source,$name_source,$usersvk_id,$text,$date,$user_id,$status,$type_status){
    //     $postmessage = new postmessage;
    //     $postmessage-> source_id = $source_id;
    //     $postmessage-> type_source = $type_source;
    //     $postmessage-> name_source = $name_source;
    //     $postmessage-> usersvk_id = $usersvk_id;
    //     $postmessage-> text = $text;
    //     $postmessage-> date = $date;
    //     $postmessage-> user_id = $user_id;
    //     $postmessage-> status = $status;
    //     $postmessage-> type_status = $type_status;
    //     $postmessage-> save();
    //     return($postmessage);
    // }
}
