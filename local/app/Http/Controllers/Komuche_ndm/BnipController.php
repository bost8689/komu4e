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
    public $mode_debug = 0; //режим отлади //if($this->mode_debug){}
    public $log_name = 'komu4e_ndm_bnip'; //публикация логов //if($this->log_name){}
    private $group_id_kndm = Null; //публикация логов //if($this->log_name){}
    private $token_moderator = Null;
    private $token_group_kndm = Null;
    public $mode_banUsersvk = 1;
    
        public function __construct()
    {
        $this->token_moderator=config('vk.token_moderator');
        $this->group_id_kndm=config('vk.group_id_kndm4');
        $this->token_group_kndm=config('vk.token_group_kndm4');
    }
   
        

	//отображение постов
    public function view(Request $request){       

        $Bnips = Bnip::with('Usersvk')->where('type_status',Null)->get();
        $c_Usersvk = collect([]);
        foreach ($Bnips as $k_bnip => $Bnip) {
            $c_Usersvk->push(['id' => $Bnip->usersvk->id,'user_id' => $Bnip->usersvk->user_id]); 
        }
        $c_Usersvk = $c_Usersvk->unique('user_id');
        if($this->mode_debug){dump('unique',$c_Usersvk);}

        //подготавливаю данные для отображения
        $c_Bnips = collect([]);
        foreach ($c_Usersvk as $k_usersvk => $v_usersvk) { //id,user_id вконтакте
            $c_Bnips->push(['Usersvk' => Usersvk::where('id','=',$v_usersvk['id'])->first(),
                'Bnips' => [
                'c_bnipTypeStatusNull'=>Bnip::with('Photosbnip')->whereNull('type_status')->where('usersvk_id',$v_usersvk['id'])->get(),
                'c_bnipTypeStatusIs'=>Bnip::with('Photosbnip')->where('usersvk_id',$v_usersvk['id'])->whereNotNull('type_status')->get()
                ]
            ]);
        } 
        //dump($c_Bnips);
        return view('komuche_ndm.bnip.view_bnip',['c_Bnips' => $c_Bnips]);
    } //end function update

    //обработка полученных данных

    public function processingBnip(Request $request){
        //dump($request->all());
        $bnips = $request->input('processingBnip');
        dump($bnips);
        foreach ($bnips as $k_bnip => $v_bnip) {
            $Bnip = Bnip::with('Usersvk','Photosbnip')->find($v_bnip['bnip_id']);
            $Bnip->status = $v_bnip['status'];
            
            //$Bnip->text = 'text'; //тут должен быть  мой отредактированный текст
            $Bnip->user_id=Auth::user()->id;
            $Bnip->save();
            //пкбликую в потеряшку

            
            //$hashteg = $hashteg.' '.'#'.'Komu4e';

            if ($v_bnip['status']=='Удалить') {
                //удаляю
                foreach ($Bnip->photosbnip as $Photobnip) {
                    if (file_exists($Photobnip->pathmax.$Photobnip->filenamemax)) {
                        unlink($Photobnip->pathmax.$Photobnip->filenamemax);
                    }
                    $Photobnip->delete();
                }                
                $Bnip -> delete(); 
            }
            elseif ($v_bnip['status']=='Найдено' and $v_bnip['typeStatus']!=Null) {                
                //публикую  
                $publicPost = $this->publicPost(array('typeStatus' => $v_bnip['typeStatus'],'status' => $v_bnip['status']),$Bnip);
                $Bnip->type_status = $v_bnip['typeStatus'];
                $Bnip->post_id=$publicPost['post_id'];
                // $Bnip->status=$v_bnip['status'];
                // $Bnip->type_status=$v_bnip['typeStatus'];
                $Bnip->type_post=$this->group_id_kndm;
                $Bnip->save();
            }
            elseif($v_bnip['status']=='Потеряно' and $v_bnip['typeStatus']!=Null){
                //публикую
                $publicPost = $this->publicPost(array('typeStatus' => $v_bnip['typeStatus'],'status' => $v_bnip['status']),$Bnip); 
                $Bnip->type_status = $v_bnip['typeStatus'];
                $Bnip->post_id = $publicPost['post_id'];
                // $Bnip->status=$v_bnip['status'];
                // $Bnip->type_status=$v_bnip['typeStatus'];
                $Bnip->type_post=$this->group_id_kndm;
                $Bnip->save();
            }
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

    public function view_message(Request $request){
        dump('message');

        //$this->token_moderator=config('vk.token_moderator');
        //$this->group_id_kndm=config('vk.group_id_kndm4');
        //if($this->mode_debug) {dump('MessageController.view');}         
        //if($this->log_write){Log::channel($this->log_name)->info('тест лог');}
        $collectPeers = collect([]);

        $params = array(
        'offset' => 0,
        'count' => 10, //по умолчанию 20, мах 200
        'filter' => 'unread',
        // all — все беседы; 
        // unread — беседы с непрочитанными сообщениями;
        // important — беседы, помеченные как важные (только для сообщений сообществ);
        // unanswered — беседы, помеченные как неотвеченные (только для сообщений сообществ). 
        'extended' => 1,//1 — возвращать дополнительные поля для пользователей и сообществ. флаг, может принимать значения 1 или 0.
        'start_message_id' => 9000,
        'group_id' => $this->group_id_kndm,
        'fields' => 'name,ban_info',        
        );
        $messagesGetConversations = VK::messagesGetConversations($this->token_group_kndm,$params,Null);
        //dd($messagesGetConversations);
        //коллекция кол-во диалогов
        $collectPeers->put('countPeers', $messagesGetConversations['count']);
        foreach ($messagesGetConversations['items'] as $k_peer => $v_peer) { 
            //присвоение данных          
            if($this->mode_debug) { dump($v_peer); }
            $a_peers[$k_peer]=$v_peer;
            
            $peer_type = $v_peer['conversation']['peer']['type']; //user или ???
            $userId = $v_peer['conversation']['peer']['id'];
            if($peer_type=='user'){
                //ищем у себя этого пользователя, если нет, то добавляем
                $Usersvk = Usersvk::where('user_id',$userId)->first();
                if(!isset($Usersvk)){ 
                    $params = array('user_ids' => $userId,'fields' => 'photo_100');
                    $usersGet = VK::usersGet($this->token_moderator,$params,Null);
                    $firstName = $usersGet[0]['first_name'];
                    $lastName = $usersGet[0]['last_name'];
                    $photo = $usersGet[0]['photo_100'];
                    $Usersvk = Usersvk::create(['user_id'=>$userId,'firstname'=>$firstName,'lastname'=>$lastName,'photo'=>$photo]);                    
                    
                }
                $a_peers[$k_peer]['Usersvk']=$Usersvk;

                //проверяю забанен ли пользователь
                $params = array(             
                'group_id' => $this->group_id_kndm,
                'offset' => 0,
                'count' => 1,// 
                'fields' => 0,//1 – возвращать сообщения в хронологическом порядке. 0 – возвращать сообщения в обратном хронологическом порядке (по умолчанию).
                'owner_id' => $userId,   
                );

                $getBanned = VK::groupsGetBanned($this->token_group_kndm,$params,Null);
                if(isset($getBanned)){
                    $a_peers[$k_peer]['banUsersvk']=$getBanned['items'][0];    
                }

            }
            else{
                dump('Сообщите администратору, что неизвестный peer_type');
            }
            
            //получение диалога
            $params= array(             
            'offset' => 0,
            'count' => 6,
            'user_id' => $userId,// 
            'rev' => 0,//1 – возвращать сообщения в хронологическом порядке. 0 – возвращать сообщения в обратном хронологическом порядке (по умолчанию).
            'group_id' => $this->group_id_kndm,//          
            );            
            $messagesGetHistory = VK::messagesGetHistory($this->token_group_kndm,$params,Null);
            if($this->mode_debug) { dump($messagesGetHistory); }
            if($this->mode_debug) { dump('кол-во сообщений', $messagesGetHistory['count']); }
            
                        
            //перебираем сообщения
            foreach ($messagesGetHistory['items'] as $k_history => $v_history) {
                $a_peers[$k_peer]['messages'][$k_history]=$v_history;

                //Узнаём кто автор сообщения из админов
                if(isset($v_history['admin_author_id'])){
                    $fromName = "Модератор";
                }
                elseif(isset($v_history['from_id']) and $v_history['from_id'] == -1*$this->group_id_kndm)
                {
                    $fromName = 'Сообщество';
                }
                elseif(!isset($v_history['admin_author_id']))
                {
                    $fromName = $Usersvk->firstname.' '.$Usersvk->lastname;
                }                 
                $a_peers[$k_peer]['messages'][$k_history]['from_name']=$fromName;
                //$a_peers[$k_peer]['messages'][$k_history]['admin_author_name']=$adminAuthorName;

                //Проверяем все вложения
                if (!empty($v_history['attachments'])) {                    
                    foreach ($v_history['attachments'] as $k_attachments => $v_attachments) {
                        if($v_attachments['type']=='photo'){                            
                            foreach ($v_attachments['photo']['sizes'] as $k_sizes => $v_sizes) {
                                if ($v_sizes['type']=='s') {
                                    $url_photo_type_s = $v_sizes['url'];//"width":75,"height":57
                                }
                                elseif($v_sizes['type']=='m') {
                                    $url_photo_type_m = $v_sizes['url'];//"width":75,"height":57
                                }
                                elseif($v_sizes['type']=='x'){
                                    $url_photo_type_x = $v_sizes['url'];//"width":130,"height":98                                            
                                }
                                elseif($v_sizes['type']=='y'){
                                    $url_photo_type_y = $v_sizes['url'];//"width":604,"height":454
                                }
                                elseif($v_sizes['type']=='z'){
                                    $url_photo_type_z = $v_sizes['url'];//"width":807,"height":606                                     
                                }
                                elseif($v_sizes['type']=='w'){ //огромный
                                    $url_photo_type_w = $v_sizes['url'];                                       
                                }
                                elseif($v_sizes['type']=='o'){
                                    $url_photo_type_o = $v_sizes['url'];//"width":959,"height":720
                                }
                                elseif($v_sizes['type']=='p'){
                                    $url_photo_type_p = $v_sizes['url'];//"width":130,"height":98
                                }
                                elseif($v_sizes['type']=='q'){
                                    $url_photo_type_q = $v_sizes['url'];//"width":200,"height":150
                                }
                                elseif($v_sizes['type']=='r'){
                                    $url_photo_type_r = $v_sizes['url'];//"width":320,"height":240
                                }
                                else{
                                    dump('неизвестный type photo $v_attachments[photo]',$v_attachments['photo']);
                                }                                
                            }//foreach
                            if(isset($url_photo_type_m)){
                                $url_photo_type_min=$url_photo_type_m;
                            }
                            elseif (isset($url_photo_type_x)) {
                                $url_photo_type_min=$url_photo_type_x;
                            }
                            elseif (isset($url_photo_type_p)) {
                                $url_photo_type_min=$url_photo_type_p;
                            }
                            elseif (isset($url_photo_type_q)) {
                                $url_photo_type_min=$url_photo_type_q;
                            }
                            elseif (isset($url_photo_type_r)) {
                                $url_photo_type_min=$url_photo_type_r;
                            }
                            elseif (isset($url_photo_type_y)) {
                                $url_photo_type_min=$url_photo_type_y;
                            }

                            if(isset($url_photo_type_y)){
                                $url_photo_type_max=$url_photo_type_y;
                            }
                            elseif (isset($url_photo_type_z)) {
                                $url_photo_type_max=$url_photo_type_z;
                            }
                            elseif (isset($url_photo_type_w)) {
                                $url_photo_type_max=$url_photo_type_w;
                            }
                            elseif (isset($url_photo_type_o)) {
                                $url_photo_type_max=$url_photo_type_o;
                            }  

                            //добавил своё представление для вывода photo
                            $a_peers[$k_peer]['messages'][$k_history]['photo'][$k_attachments]['url_photo_type_min']=$url_photo_type_min;
                            $a_peers[$k_peer]['messages'][$k_history]['photo'][$k_attachments]['url_photo_type_max']=$url_photo_type_max;              

                        }//type ==  photo
                    }
                }

            }
            //далее вывожу все диалоги ...пользователя 
        }
        if(!empty($a_peers)){
            $collectPeers->put('peers',$a_peers);
        }  
        if($this->mode_debug) { dump($collectPeers); }
        dump($collectPeers); 
        return view('komuche_ndm.bnip.view_messages_bnip',['peers' => $collectPeers]); 
        //dump($messagesGetHistory);
    }

    public function processing_message(Request $request){
        dump('processing_message');
        dump($request->all());
        $text=Null;
        $photo=Null;
        
        

        foreach ($request->input('bnip') as $k_bnip => $v_bnip) {
            
            $Usersvk = Usersvk::find($v_bnip['usersvk_id']);
            //если в массиве есть сообщения
            if (array_key_exists("message", $v_bnip)) {
                
                foreach ($v_bnip['message'] as $k_message => $v_message) {
                    if (array_key_exists("text", $v_message)) {
                        $text .= $v_message['text'].' ';    
                    }

                    if(array_key_exists("photo", $v_message)){
                        foreach ($v_message['photo'] as $k_photo => $urlPhoto) {
                            $photo[$k_photo]=$urlPhoto;
                        }
                    }
                }
                
            }

            if (array_key_exists("message", $v_bnip)) {
                $Bnip = Bnip::create(['source_id'=>Null,'type_source'=>'message','post_id'=>Null,'type_post'=>Null,'usersvk_id'=>$Usersvk->id,'text'=>$text,'user_id'=>Auth::user()->id,'status'=>Null,'type_status'=>Null]);
                if (!empty($photo)) {
                    foreach ($photo as $k_photo => $urlPhoto) {
                    $img =Image::make($urlPhoto);
                    $pathMax = 'public/komu4e_ndm/bnip/naideno/';
                    $fileNameMax = 'm'.$Bnip->id.'_'.$k_photo.'.'.'jpg';
                    $img->insert('public/bnipWatermark.png', 'bottom-right')->save($pathMax.$fileNameMax);
                    Photosbnip::create(['filenamemax'=>$fileNameMax,'pathmax'=>$pathMax,'bnip_id'=>$Bnip->id]);
                    }
                }
                $text=Null;
                $photo=Null;
            }
            
        }
        //return redirect()->route('view_bnip'); 
    }
   
}//end class