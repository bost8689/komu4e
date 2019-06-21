<?php

namespace Komu4e\Model\komuche_ndm;

use Illuminate\Database\Eloquent\Model;
use Komu4e\Http\Controllers\VK;

class Usersvk extends Model
{
  protected $table = 'usersvk';
  public $timestamps = FALSE;
  //разрешаю записывать значения
  protected $fillable = array('user_id', 'firstname', 'lastname','photo');

  public function order(){
    return $this->hasMany('Komu4e\Model\Komuche_ndm\Order');
  }
  public function postmessage(){
    return $this->hasMany('Komu4e\Model\Komuche_ndm\Postmessage');
  }
  public function bnip(){
    return $this->hasMany('Komu4e\Model\Komuche_ndm\Bnip');
  }

  // Static function add_usersvk($user_id,$first_name,$last_name){    
  //   $usersvk = new usersvk;
  //   $usersvk-> user_id = $user_id;
  //   $usersvk-> firstname = $first_name;
  //   $usersvk-> lastname = $last_name;
  //   $usersvk-> save();
  //   return($usersvk);
  // }
  //функция поиска в базе пользователя, при отсутствии добавляет значения и фото
/*  Static function find_usersvk($user_id){
    $usersvk = Usersvk::where('user_id', '=', $user_id)->first();     
    if (isset($usersvk)){ //пользователь найден в БД
      return (['usersvk'=>$usersvk,'status'=>'']);  
    }
    else{
      if ($user_id>0) { //юзер
          $usersget = VK::usersget(config('vk.admin_token'),$user_id);          
          $firstname = $usersget[0]['first_name'];
          $lastname = $usersget[0]['last_name'];
          $photo = $usersget[0]['photo_100'];                        
      }
      else{ //группа
          $groupsgetById = VK::groupsgetById(config('vk.admin_token'),abs($user_id));          
          $firstname = $groupsgetById[0]['name'];              
          $lastname = '';
          $photo = $groupsgetById[0]['photo_100'];
      }            
      $usersvk = Usersvk::create(['user_id' => $user_id,'firstname' => $firstname,'lastname' => $lastname,'photo' => $photo]);
      return (['usersvk'=>$usersvk,'status'=>'добавлен']);     
    }
  }*/

  // $user = Usersvk::firstOrCreate(
  //   ['user_id' => $from_id],
  //   ['firstname' => "Test"],
  //   ['lastname' => "Test"]
  // );
}
