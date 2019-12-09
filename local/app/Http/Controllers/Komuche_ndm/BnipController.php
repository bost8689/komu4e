<?php
namespace Komu4e\Http\Controllers\Komuche_ndm;

use Illuminate\Http\Request;
use Komu4e\Http\Controllers\Controller;
//use Komu4e\Http\Controllers\Komuche_ndm\UpdateEventController;
use Komu4e\Http\Controllers\VK;

//use Komu4e\Model\Komuche_ndm\Order;
use Komu4e\Model\Komuche_ndm\Bnip;
use Komu4e\Model\Komuche_ndm\Photosbnip;
use Komu4e\Model\Komuche_ndm\Usersvk;
//use Komu4e\Model\Komuche_ndm\Postmessage;
//use Komu4e\Model\Komuche_ndm\Photospostmessage;
use Komu4e\Model\Komuche_ndm\Settings;

use Komu4e\User;
use Auth;
use Log;
//use Session;

use VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor;
use VK\CallbackApi\VKCallbackApiHandler;
use VK\Client\VKApiClient;
use VK\Client\VKApiRequest;
use VK\Exceptions\VKApiException;

use Image;
use Intervention\Image\ImageManager;



// class CallbackApiMyHandler extends VKCallbackApiHandler { 
//  //    public function messageNew ($group_id,$secert_key,$object) { 
//  //     dump('Сработало...');
//  //        dump($object); 
//  //    } 
//  // public function wallPostNew ($group_id,$secert_key,$object) { 
//  //        dump($object); 
//  //    } 
//  //    public function group_join ($group_id,$secert_key,$object) { 
//  //     Log::info('Было втспление в группу');
//  //        dump($object); 
//  //    }     
// }

        /*//не распознает с Null
        if (isset($checkbox_all)) {
            dump('isset истина checkbox');
        }
        //распознает и с Null и без него
        if (empty($checkbox_all)) {
            dump('empty истина checkbox');
        }*/

class BnipController extends Controller
{	
    public $log_write = 1; //публикация логов //if($this->log_write){}
    public $mode_debug = 1; //режим отлади //if($this->mode_debug){}
    public $log_name = 'komu4e_ndm_bnip'; //публикация логов //if($this->log_name){}
    private $group_id_kndm = Null; //публикация логов //if($this->log_name){}
    private $token_moderator = Null;
    private $token_group_kndm = Null;
    private $debug = array();
    //public $mode_banUsersvk = 1;
    
        public function __construct()
    {
        $this->token_moderator=config('vk.token_moderator');
        $this->group_id_kndm=config('vk.group_id_kndm4'); //bnip
        $this->token_group_kndm=config('vk.token_group_kndm4'); //bnip        
    }
   
        

	//отображение постов
    public function view(Request $request){       
        $debug['Отладка']="BnipController.view";
        //Посты со статусом NULL
        $Bnips = Bnip::with('Usersvk')->where('type_status',Null)->get();

        //получаю уникальных пользователей
        $cUsersvk = collect([]);
        foreach ($Bnips as $kBnip => $Bnip) {
            $cUsersvk->push(['id' => $Bnip->usersvk->id,'user_id' => $Bnip->usersvk->user_id]); 
        }
        $cUsersvk = $cUsersvk->unique('user_id');
        $debug['Коллекция уникальных $cUsersvk']=$cUsersvk;        

        //подготавливаю данные для отображения
        $cBnips = collect([]);
        foreach ($cUsersvk as $k_usersvk => $v_usersvk) { //id,user_id вконтакте
            $cBnips->push(['Usersvk' => Usersvk::where('id','=',$v_usersvk['id'])->first(),
                'Bnips' => [
                'cBnipTypeStatusNull'=>Bnip::with('Photosbnip')->whereNull('type_status')->where('usersvk_id',$v_usersvk['id'])->get(),
                'cBnipTypeStatusIs'=>Bnip::with('Photosbnip')->where('usersvk_id',$v_usersvk['id'])->whereNotNull('type_status')->get()
                ]
            ]);
        } 
        $debug['Коллекция уникальных бюро находок $cBnips']=$cBnips;
        
        //Отладка
        if($this->mode_debug){
            dump($debug);
        }
        return view('komuche_ndm.bnip.view_bnip',['cBnips' => $cBnips]);
    } //end function update

    //обработка полученных данных
    public function processingBnip(Request $request){

        $debug['Отладка']="BnipController.processingBnip";
        $debug['Входящие $request->all()']=$request->all();
        //Отладка
        if($this->mode_debug){
            dump($debug);
        }        
        $aBnips = $request->input('processingBnip');
        //dump($bnips);
        foreach ($aBnips as $kBnip => $vBnip) {
            $Bnip = Bnip::with('Usersvk','Photosbnip')->find($vBnip['bnip_id']);
            $Bnip->status = $vBnip['status'];
            $Bnip->user_id=Auth::user()->id;
            $Bnip->save();

            //пкбликую в потеряшку
            if ($vBnip['status']=='Удалить') {
                //удаляю
                foreach ($Bnip->photosbnip as $Photobnip) {
                    if (file_exists($Photobnip->pathmax.$Photobnip->filenamemax)) {
                        unlink($Photobnip->pathmax.$Photobnip->filenamemax);
                    }
                    $Photobnip->delete();
                }                
                $Bnip -> delete(); 
            }
            elseif ($vBnip['status']=='Повтор') {
                //
            }
            elseif ($vBnip['status']=='Ошибка группой') {
                //
            }
            elseif ($vBnip['status']=='Найдено' and $vBnip['typeStatus']!=Null) {                
                //публикую  
                $publicPost = $this->publicPost(array('typeStatus' => $vBnip['typeStatus'],'status' => $vBnip['status']),$Bnip);
                $debug['Потеряшка $Bnip']=$Bnip;
                $debug['Результат публикации поста $publicPost']=$publicPost;
                $Bnip->type_status = $vBnip['typeStatus'];
                $Bnip->post_id=$publicPost['post_id'];
                $Bnip->type_post=$this->group_id_kndm;
                $Bnip->save();
                //отправка сообщения
                $message='Здравствуйте, мы разместили Вашу запись.'."\n".
                "ссылка на ваш пост https://vk.com/wall-"."133501081"."_".$publicPost['post_id']."\n".
                "Проверьте, чтобы ваша личка была открыта и Вам смогли написать. Будьте бдительны, мошенники существуют, проверяйте информацию."."\n".
                'С Уважением команда КомуЧё'."\n"."\n".
                "Чтобы удалить свой пост, напишите слово удалить в комментариях под своим постом по этой ссылке -> "."https://vk.com/wall-"."133501081"."_".$publicPost['post_id'];
                $debug['Сообщение для отправки $message']=$message;
                $this->sendMessage(array('message' => $message),$Bnip->Usersvk);
            }
            elseif($vBnip['status']=='Потеряно' and $vBnip['typeStatus']!=Null){
                //публикую
                $publicPost = $this->publicPost(array('typeStatus' => $vBnip['typeStatus'],'status' => $vBnip['status']),$Bnip); 
                $debug['Потеряшка $Bnip']=$Bnip;
                $debug['Результат публикации поста $publicPost']=$publicPost;
                $Bnip->type_status = $vBnip['typeStatus'];
                $Bnip->post_id = $publicPost['post_id'];
                // $Bnip->status=$vBnip['status'];
                // $Bnip->type_status=$vBnip['typeStatus'];
                $Bnip->type_post=$this->group_id_kndm;
                $Bnip->save();
                //отправка сообщения
                $message='Здравствуйте, мы разместили Вашу запись.'."\n".
                "ссылка на ваш пост https://vk.com/wall-"."133501081"."_".$publicPost['post_id']."\n".
                "Проверьте, чтобы ваша личка была открыта и Вам смогли написать. Будьте бдительны, мошенники существуют, проверяйте информацию."."\n".
                "С Уважением команда КомуЧё"."\n"."\n".
                "Чтобы удалить свой пост, напишите слово удалить в комментариях под своим постом по этой ссылке -> "."https://vk.com/wall-"."133501081"."_".$publicPost['post_id'];
                $debug['Сообщение для отправки $message']=$message;
                $this->sendMessage(array('message' => $message),$Bnip->Usersvk);
            }
        } 

        //Отладка
        if($this->mode_debug){
            dd($debug);
        }
        return redirect()->route('home');     

    }

    public function publicPost(array $arr_data,$Bnip){
        $attachments=Null;

        $hashteg = '#'.$arr_data['typeStatus'].'_'.$arr_data['status'].'@'.'bnip_nadym';
        $hashteg = $hashteg.' '.'#'.$arr_data['status'].'@'.'bnip_nadym';
        $hashteg = $hashteg.' '.'#'.'Потеряшка';
        $hashteg = $hashteg.' '.'#'.'КомуЧё';
        $hashteg = $hashteg.' '.'#'.'Надым';

        $params = array(             
            'group_id' => $this->group_id_kndm,//идентификатор сообщества, на стену которого нужно загрузить фото (без знака «минус»).
        );
        $photosGetWallUploadServer = VK::photosGetWallUploadServer($this->token_moderator,$params,array('log_name'=>$this->log_name));
        $uploadUrl = $photosGetWallUploadServer['upload_url'];

        foreach ($Bnip->photosbnip as $Photobnip) {
            //$a_photos[]=$Photobnip->pathmax.$Photobnip->filenamemax; 
            //$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$value_photo
            $params = array('uploadUrl' => $uploadUrl,'typeFile'=>'photo','fileName'=>$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$Photobnip->pathmax.$Photobnip->filenamemax);
            $requestUpload = VK::requestUpload($params,Null); 
            // dd($requestUpload);
            $params = array(             
                'group_id' => $this->group_id_kndm,//идентификатор сообщества, на стену которого нужно загрузить фото (без знака «минус»).
                'photo' => $requestUpload['photo'],
                'server' => $requestUpload['server'],
                'hash' => $requestUpload['hash'],
            );
            $photosSaveWallPhoto = VK::photosSaveWallPhoto($this->token_moderator, $params, Null);
            //dd($photosSaveWallPhoto);
            $attachments.='photo'.$photosSaveWallPhoto[0]['owner_id'].'_'.$photosSaveWallPhoto[0]['id'].',';                     
        }

        $params = array(             
                'owner_id' => -1 * $this->group_id_kndm, //у группы отрицательное поле
                'friends_only' => 0, //1 — запись будет доступна только друзьям, 0 — всем пользователям. По умолчанию публикуемые записи доступны всем пользователям. флаг, может принимать значения 1 или 0
                'from_group' => 1,//1 — запись будет опубликована от имени группы, 0 — запись будет опубликована от имени пользователя (по умолчанию). 
                'message' => $hashteg."\n".$Bnip->text."\n".'Контакты: '.'https://vk.com/id'.$Bnip->usersvk->user_id,
                'attachments' => $attachments,
                'signed' => 0, //1 — у записи, размещенной от имени сообщества, будет добавлена подпись (имя пользователя, разместившего запись), 0 — подписи добавлено не будет. Параметр учитывается только при публикации на стене сообщества и указании параметра 
                'publish_date' => Null,
            );
        $wallPost = VK::wallPost($this->token_moderator,$params,Null);
        return $wallPost;
    } 

    //отправка сообщений
    public function sendMessage(array $arrData,$Usersvk){
        $params = array(             
            'user_id' => $Usersvk->user_id,
            'message' => $arrData['message'], //220409092 Вячеслав Тихонов
            'random_id'=> rand(), //рандомное число
            //'group_ids' => $group_ids,    
        );
        $messagesSend = VK::messagesSend($this->token_group_kndm,$params,Null);       
    }
   
}//end class