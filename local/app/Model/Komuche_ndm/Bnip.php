<?php

namespace Komu4e\Model\Komuche_ndm;

use Illuminate\Database\Eloquent\Model;

class Bnip extends Model
{
    protected $table = 'bnip';    
    protected $fillable = array('source_id', 'type_source','name_source','post_id','type_post','name_post','usersvk_id','text','user_id','status','type_status');

    public function photosbnip(){
        return $this->hasMany('Komu4e\Model\Komuche_ndm\Photosbnip');
    }
    public function usersvk()
    {
        return $this->belongsTo ('Komu4e\Model\Komuche_ndm\Usersvk');
    }
    public function user(){
        return $this->belongsTo('Komu4e\User');
    }

    // $Bnip = Bnip::create(['source_id'=>$source_id,'type_source'=>$type_source,'name_source'=>$name_source,'post_id'=>$post_id,'type_post'=>$type_post,'name_post'=>$name_post,'usersvk_id'=>$usersvk_id,'text'=>$text,'user_id'=>$user_id,'status'=>$status,'type_status'=>$type_status]);

    // static function add($source_id,$type_source,$name_source,$post_id,$type_post,$name_post,$usersvk_id,$text,$date,$user_id,$status,$type_status){
    //     $add = new bnip;
    //     $add-> source_id = $source_id;
    //     $add-> type_source = $type_source;
    //     $add-> name_source = $name_source;
    //     $add-> post_id = $post_id;
    //     $add-> type_post = $type_post;
    //     $add-> name_post = $name_post;
    //     $add-> usersvk_id = $usersvk_id;
    //     $add-> text = $text;
    //     $add-> date = $date;
    //     $add-> user_id = $user_id;
    //     $add-> status = $status;
    //     $add-> type_status = $type_status;    
    //     $add-> save();
    // return($add);
    // }
}
