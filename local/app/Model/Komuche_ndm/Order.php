<?php

namespace Komu4e\Model\Komuche_ndm;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //чтобы метка ставилась на удаление
	/*use SoftDeletes;*/
	protected $table = 'orders';
    protected $primaryKey = 'id'; 
    protected $fillable = array('usersvk_id', 'type','price','ordered','executed','status','comments','user_id');   
    //так как таблица содержит автоатрибут автодобавление
    public $incrementing = FALSE;    
    //чтобы автоматически заполня
    public $timestamps = TRUE;

    // $Order = Order::create(['usersvk_id'=>$usersvk_id,'type'=>$type,'price'=>$price,'ordered'=>$ordered,'executed'=>$executed,'status'=>$status,'comments'=>$comments,'user_id'=>$user_id]);
    
    //protected $dates = ['deleted_at'];       
    //список достпных для записи полей
    /*protected $fillable = ['name'];*/
    
    //список запрещенных для записи полей
    /*protected $guarded = ['*'];*/
    /*public function users_vk(){
    //связываю с одной записбю c моделью
	return $this->hasOne('App\User_vk');
	}*/
	public function usersvk(){
		return $this->belongsTo('Komu4e\Model\Komuche_ndm\Usersvk');
	}

    public function user(){
        return $this->belongsTo('Komu4e\User');
    }    
}
