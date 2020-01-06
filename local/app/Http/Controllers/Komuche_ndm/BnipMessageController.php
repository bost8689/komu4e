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

class BnipMessageController extends Controller
{	
    //public $log_write = 1; //публикация логов //if($this->log_write){}
    public $mode_debug = 0; //режим отлади //if($this->mode_debug){}
    public $log_name = 'BnipMessageController'; //публикация логов //if($this->log_name){}
    private $group_id_kndm = Null; //публикация логов //if($this->log_name){}
    private $token_moderator = Null;
    private $token_group_kndm = Null; //потеряшка
    private $debug = array();
    //public $mode_banUsersvk = 1;
    
        public function __construct()
    {
        $this->token_moderator=config('vk.token_moderator');
        $this->group_id_kndm=config('vk.group_id_kndm4'); //bnip
        $this->token_group_kndm=config('vk.token_group_kndm4'); //bnip        
    }

    //отобразить сообщения и обработать диалоги
    public function view_message(Request $request){

        //получаю коллекцию не прочитанных диалогов
        $collectPeers = collect([]);
        $params = array(
        'offset' => 0,
        'count' => 200, //по умолчанию 20, мах 200
        'filter' => 'unread',
        // all — все беседы; 
        // unread — беседы с непрочитанными сообщениями;
        // important — беседы, помеченные как важные (только для сообщений сообществ);
        // unanswered — беседы, помеченные как неотвеченные (только для сообщений сообществ). 
        'extended' => 1,//1 — возвращать дополнительные поля для пользователей и сообществ. флаг, может принимать значения 1 или 0.
        //'start_message_id' => 19000,
        'group_id' => $this->group_id_kndm,
        'fields' => 'name,ban_info',        
        );
        $messagesGetConversations = VK::messagesGetConversations($this->token_group_kndm,$params,Null);

        //перебираю коллекцию диалогов
        $collectPeers->put('countPeers', $messagesGetConversations['count']);
        foreach ($messagesGetConversations['items'] as $k_peer => $v_peer) { 
            //присвоение данных          
            //if($this->mode_debug) { dump($v_peer); }
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
                dump('Сообщите администратору, что неизвестный peer_type',$v_peer['conversation']['peer']['type']);
            }
            
            // dd($a_peers);
            //Получение данных по конкретному диалогу
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
        dump($collectPeers);
        if($this->mode_debug) { dump($collectPeers); }
        //dump($collectPeers); 
        return view('komuche_ndm.bnip.view_messages_bnip',['peers' => $collectPeers]); 
        //dump($messagesGetHistory);
    }

    //view -> 
    public function processing_message(Request $request){
        //dump('processing_message');
        //dump($request->all());
        foreach ($request->input('bnip') as $k_bnip => $v_bnip) {
            //dump($v_bnip);
            $text=Null;
            $photo=Null;
            $k=0;
            $Usersvk = Usersvk::find($v_bnip['usersvk_id']);
            //если в массиве есть сообщения
            if (array_key_exists("message", $v_bnip)) {
                //dump('message',$v_bnip);
                foreach ($v_bnip['message'] as $v_message) {
                    if (array_key_exists("text", $v_message)) {
                        $text .= $v_message['text'].' ';    
                    }

                    if(array_key_exists("photo", $v_message)){
                        foreach ($v_message['photo'] as $urlPhoto) {
                            $photo[$k++]=$urlPhoto;
                            //dump('$photo ',$photo);
                            //dump($urlPhoto);
                        }
                    }
                }                
            }
            if ($v_bnip['status']=='Найдено') {
                if (array_key_exists("message", $v_bnip)) {
                    $this->create_bnip_from_message(array('status'=>$v_bnip['status'],'photo' =>$photo,'text'=>$text),$Usersvk);                    
                }
            }
            elseif($v_bnip['status']=='Потеряно'){
                //если есть message
                //dump($v_bnip);
                if (array_key_exists("message", $v_bnip)) {
                    //dd('Потеряно');
                    $this->create_bnip_from_message(array('status'=>$v_bnip['status'],'photo' =>$photo,'text'=>$text),$Usersvk);
                    // $message='Здравствуйте, мы разместили Вашу запись.'."\n".'С Уважением команда КомуЧё';
                    // $this->send_message(array('message' => $message),$Usersvk);
                }
            }
            elseif($v_bnip['status']=='Ошибка'){
                //отправляю сообщение, что Вы ошиблись группой
                $message='Здравствуйте, возможно Вы ошиблись группой. Потеряшка - это находки и потери'."\n".'
                    По всем вопросам пишите в сообщения соответствующего сообщества. Например КомуЧё Надым по этой ссылке https://vk.com/im?sel=-117556337'."\n".'
                    Наши сообщества:'."\n".'
                    КомуЧё Надым - городское сообщество. https://vk.com/komuche_nadym'."\n".'
                    КомуЧё Авто - попутчики, грузо и пассажироперевозки https://vk.com/auto_nadym'."\n".'
                    КомуЧё Потеряшка - бюро находок и потерь https://vk.com/bnip_nadym'."\n".'
                    КомуЧё Мамочки https://vk.com/komuche_mamomochki'."\n".'
                    КомуЧё Объявления - https://vk.com/komuche'."\n".'
                    С Уважением команда КомуЧё';
                $this->send_message(array('message' => $message),$Usersvk);
            }
            elseif($v_bnip['status']=='Повтор'){
                 $message='Здравствуйте, Вашу похожую запись уже размещали, мы стараемся размещать только уникальные записи.'."\n".'С Уважением команда КомуЧё';
                 $this->send_message(array('message' => $message),$Usersvk);


                //отправляю сообщение, что Вы ошиблись группой
            }
            
        }
        /*VK::messagesmarkAsRead($access_token_bnip,$message_ids); //делаю сообщения прочитанными*/

        return redirect()->route('view_bnip'); 
    }


    public function create_bnip_from_message(array $arrData,$Usersvk){
        $Bnip = Bnip::create(['source_id'=>$Usersvk->user_id,'type_source'=>'user','post_id'=>Null,'type_post'=>Null,'usersvk_id'=>$Usersvk->id,'text'=>$arrData['text'],'user_id'=>Auth::user()->id,'status'=>$arrData['status'],'type_status'=>Null]);
        if ($arrData['status']=='Найдено') {
            $pathMax = 'public/komu4e_ndm/bnip/naideno';
        }
        elseif($arrData['status']=='Потеряно'){
            $pathMax = 'public/komu4e_ndm/bnip/poteryano';
        }        
        if (!empty($arrData['photo'])) {
            foreach ($arrData['photo'] as $k_photo => $urlPhoto) {
            $img =Image::make($urlPhoto);
            $fileNameMax = 'm'.$Bnip->id.'_'.$k_photo.'.'.'jpg';
            $img->insert('public/bnipWatermark.png', 'bottom-right')->save($pathMax.$fileNameMax);
            Photosbnip::create(['filenamemax'=>$fileNameMax,'pathmax'=>$pathMax,'bnip_id'=>$Bnip->id]);
            }
        }
    }

    public function send_message(array $arrData,$Usersvk){
        $params = array(             
            'user_id' => $Usersvk->user_id,
            'message' => $arrData['message'], //220409092 Вячеслав Тихонов
            'random_id'=> rand(), //рандомное число
            //'group_ids' => $group_ids,    
        );
        $messagesSend = VK::messagesSend($this->token_group_kndm,$params,Null);       
    }
   
}//end class