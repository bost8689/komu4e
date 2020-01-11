<?php

namespace Komu4e\Http\Controllers\Komuche_ndm;

use Komu4e\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Komu4e\Http\Controllers\VK;

//use Komu4e\Model\Komuche_ndm\Order;
//use Komu4e\Model\Komuche_ndm\Bnip;
//use Komu4e\Model\Komuche_ndm\Photosbnip;
use Komu4e\Model\Komuche_ndm\Usersvk;
use Komu4e\Model\Komuche_ndm\Postmessage;
//use Komu4e\Model\Komuche_ndm\Photospostmessage;
use Komu4e\Model\Komuche_ndm\Settings;

class FindPostsController extends Controller
{
    public $mode_debug = 0; //режим отлади //if($this->mode_debug){}
    public $log_name = 'FindPostsController'; //публикация логов //if($this->log_name){} 
    private $token_moderator = Null;
    private $group_id_kndm = Null;
    private $token_group_kndm = Null;


        public function __construct()
    {

        $SettingsModeDebug=Settings::where('name','komu4e_ndm_debug_mode')->first();
        if ($SettingsModeDebug->value2=="Включено") {
            $this->mode_debug = 1;
        }

    }


        public function view(Request $request)
    {
    	return view('komuche_ndm.findposts.view_FindPostmessage');
        
    }
        public function processing(Request $request)
    {
    	dump($request->all());
        $this->token_moderator=config('vk.token_moderator');

    	//$access_token=config('vk.admin_token');
    	$link_usersvk=trim($request->input('link_usersvk')); //удаляю пробелы 
        
    	$date_begin = $request->input('date_begin');      
        $pos1 = strpos($link_usersvk, 'https', 0); // $pos = 7, not 0
        $pos2 = strpos($link_usersvk, 'vk.com/', 0); // $pos = 7, not 0     
        if ($pos1 === false or $pos2 === false) {
            // dump('не найден https или vk.com');
            $result_find='Не правильно вставлена ссылка';
            dd($result_find);
            //return view('postmessage.view_FindPostmessage',['result_find'=> $result_find]);            
        } 
        else {
            $user_id=substr($link_usersvk,15); //обрезаю с 15 символа
            $params = array('user_ids' => $user_id,'fields' => 'photo_100');
            $users_get = VK::usersGet($this->token_moderator,$params,Null);

            if (isset($users_get['error'])) {
                dd($users_get);
             	//return view('postmessage.view_FindPostmessage',['result_find'=> $result_usersget['error']]);
            }            

            $user_id=$users_get[0]['id'];    
            $Usersvk = Usersvk::where('user_id',$user_id)->first();

            if(!isset($Usersvk)){ 
                $first_name = $users_get[0]['first_name'];
                $last_name = $users_get[0]['last_name'];
                $photo = $users_get[0]['photo_100'];
                $Usersvk = Usersvk::create(['user_id'=>$user_id,'firstname'=>$first_name,'lastname'=>$last_name,'photo'=>$photo]); 
            }

        	$Postmessages = Postmessage::with('photospostmessage','usersvk','user')->where('usersvk_id', $Usersvk->id)->where('date','>',$date_begin.' 00:00:01')->orderBy('date', 'desc')->get();
            dump($Postmessages);
        	return view('komuche_ndm.findposts.processing_findpostmessage',['Postmessages'=>$Postmessages,'Usersvk'=>$Usersvk]);            

        }

    	

        
    }

}
