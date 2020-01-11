<?php
namespace Komu4e\Http\Controllers\Komuche_ndm;

use Illuminate\Http\Request;
use Komu4e\Http\Controllers\Controller;
use Komu4e\Http\Controllers\VK;

//use Komu4e\Model\Komuche_ndm\Order;
// use Komu4e\Model\Komuche_ndm\Bnip;
// use Komu4e\Model\Komuche_ndm\Photosbnip;
// use Komu4e\Model\Komuche_ndm\Usersvk;
//use Komu4e\Model\Komuche_ndm\Postmessage;
//use Komu4e\Model\Komuche_ndm\Photospostmessage;
//use Komu4e\Model\Komuche_ndm\Settings;

use Komu4e\User;
use Auth;
use Log;
//use Session;

// use VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor;
// use VK\CallbackApi\VKCallbackApiHandler;
// use VK\Client\VKApiClient;
// use VK\Client\VKApiRequest;
// use VK\Exceptions\VKApiException;

// use Image;
// use Intervention\Image\ImageManager;


class CallbackController extends Controller
{	
    public $mode_debug = 0; //режим отлади //if($this->mode_debug){}    
    public $log_write = 1; //публикация логов //if($this->log_write){}
    public $log_name = 'komu4e_ndm_callback'; //публикация логов //if($this->log_name){}
    private $group_id_kndm = Null; //публикация логов //if($this->log_name){}
    private $token_moderator = Null;
    private $answer = Null;
    private $secret = Null;
    
        public function __construct()
    {

        $SettingsModeDebug=Settings::where('name','komu4e_ndm_debug_mode')->first();
        if ($SettingsModeDebug->value2=="Включено") {
            $this->mode_debug = 1;
        }

        $this->token_moderator=config('vk.token_moderator');
        $this->group_id_kndm=config('vk.group_id_kndm5');

        $this->secret=config('vk.secret_kndm5');  
        $this->answer=config('vk.answer_kndm5');
    }

	//отображение постов
    public function index(Request $request){       
        
        $access_token = $this->token_moderator;
        $data_encode = json_encode($request->all());
        $data = json_decode($data_encode); 
        Log::channel($this->log_name)->info($data_encode);


        if($data->type=='confirmation' and $data->secret==$this->secret){
            return $this->answer; //test
        }
        elseif ($data->secret!=$this->secret) { //для безопасности
            return 'ok';
        }
    } //end function update

    /*public function savePhoto(array $arr_data,$Postmessage){

        foreach ($Postmessage->photospostmessage as $k_photo => $Photo) {
            $typeMax = 'jpg';
            $fileNameMax = $Postmessage->id.'_'.$k_photo.'.'.$typeMax;
            if ($arr_data['type']=='postmessage') {
                $pathMax = 'public/komu4e_ndm/postmessage/';
                                
            }
            elseif($arr_data['type']=='Найдено'){
                $pathMax = 'public/komu4e_ndm/bnip/naideno/';

            }
            elseif($arr_data['type']=='Потеряно'){
                $pathMax = 'public/komu4e_ndm/bnip/poteryano/';

            }
            elseif($arr_data['type']=='Заказ'){
                $pathMax = 'public/komu4e_ndm/zakaz/';

            }
            else{
                dump('Неизвестный тип $pathMax сообщите администратору, сделайте скрин этой ошибки.'.$pathMax);
            }
            $img =Image::make($Photo->photomax_url)->save($pathMax.$fileNameMax);
            $Photo->filenamemax = $fileNameMax;
            $Photo->pathmax = $pathMax;
            $Photo->typemax = $typeMax;
            $Photo->save();            
        }
        return;   
    }*/

}//end class