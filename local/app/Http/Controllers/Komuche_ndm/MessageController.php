<?php

namespace Komu4e\Http\Controllers\Komuche_ndm;
set_time_limit(60);

use Illuminate\Http\Request;
use Komu4e\Http\Controllers\Controller;
use Komu4e\Http\Controllers\VK;

//use Komu4e\Model\Komuche_ndm\Order;
//use Komu4e\Model\Komuche_ndm\Bnip;
//use Komu4e\Model\Komuche_ndm\Photosbnip;
use Komu4e\Model\Komuche_ndm\Usersvk;
//use Komu4e\Model\Komuche_ndm\Postmessage;
//use Komu4e\Model\Komuche_ndm\Photospostmessage;
use Komu4e\Model\Komuche_ndm\Settings;

use Komu4e\User;
use Auth;
use Log;
use Session;
use Lang;

use VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor;
use VK\CallbackApi\VKCallbackApiHandler;
use VK\Client\VKApiClient;
use VK\Client\VKApiRequest;
use VK\Exceptions\VKApiException;


/*class CallbackApiMyHandler extends VKCallbackApiHandler { 
//    public function messageNew ($group_id,$secert_key,$object) { 
//     dump('Сработало...');
//        dump($object); 
//    } 
// public function wallPostNew ($group_id,$secert_key,$object) { 
//        dump($object); 
//    } 
//    public function group_join ($group_id,$secert_key,$object) { 
//     Log::info('Было втспление в группу');
//        dump($object); 
//    }     
}*/

class MessageController extends Controller
{	
  public $log_write = 0; //публикация логов //if($this->log_write){}
  public $mode_debug = 0; //режим отлади //if($this->mode_debug){}
  public $log_name = 'komuche_ndm_message'; //публикация логов //if($this->log_name){} 
  public $token_moderator;
  public $group_id_kndm;
  public $token_group_kndm;
  public $Usersvk;
  public $message;
  public $requests = array(); //массив заявко

  public function __construct(Request $request)
  {
   $SettingsModeDebug=Settings::where('name','komu4e_ndm_debug_mode')->first();
   if ($SettingsModeDebug->value2=="Включено") {
    $this->mode_debug = 1;
          //if($this->mode_debug) { dump($v_peer); }
  }

  $this->token_moderator=config('vk.token_moderator');
  switch ($request->input('group_type')) {
    case 'Объявления':
    $this->group_id_kndm=config('vk.group_id_kndm1');
    $this->token_group_kndm=config('vk.token_group_kndm1');
    break;
    case 'Городская':
    $this->group_id_kndm=config('vk.group_id_kndm2');
    $this->token_group_kndm=config('vk.token_group_kndm2');
    break;
    default:            
    break;
  }  
}

  //Проверить забанен ли юзер
public function checkUserBannedGroups (){
      //проверяю забанен ли пользователь
 $params = array(             
      'group_id' => config('vk.group_id_kndm1'), //в объявлениях
      'offset' => 0,
      'count' => 1,// 
      'fields' => 0,//1 – возвращать сообщения в хронологическом порядке. 0 – возвращать сообщения в обратном хронологическом порядке (по умолчанию).
      'owner_id' => $this->Usersvk->user_id,   
    );
 $groupsGetBanned = VK::groupsGetBanned(config('vk.token_group_kndm1'),$params,Null);
 return $groupsGetBanned;
}

  //получить диалоги с не прочитанными сообщениями и сформировать
public function getPeers (){
 $params = array(
  'offset' => 0,
      'count' => 30, //по умолчанию 20, мах 200
      'filter' => 'unread',
      // all — все беседы; 
      // unread — беседы с непрочитанными сообщениями;
      // important — беседы, помеченные как важные (только для сообщений сообществ);
      // unanswered — беседы, помеченные как неотвеченные (только для сообщений сообществ). 
      'extended' => 1,//1 — возвращать дополнительные поля для пользователей и сообществ. флаг, может принимать значения 1 или 0.
      //'start_message_id' => 10000,
      'group_id' => $this->group_id_kndm,
      'fields' => 'name,ban_info',        
    );
      //dd($this->token_group_kndm);
 $messagesGetConversations = VK::messagesGetConversations($this->token_group_kndm,$params,Null);
 return $messagesGetConversations;
}

  //получение истории диалога
public function messagesGetHistory(){
 $params= array(             
  'offset' => 0,
  'count' => 6,
  'user_id' => $this->Usersvk->user_id,// 
  'rev' => 0,//1 – возвращать сообщения в хронологическом порядке. 0 – возвращать сообщения в обратном хронологическом порядке (по умолчанию).
  'group_id' => $this->group_id_kndm,//          
  );         
 $messagesGetHistory = VK::messagesGetHistory($this->token_group_kndm,$params,Null);
 return $messagesGetHistory;
}

public function dataGenerationForView(){

}

public function getUsersInDB($data){
 $Usersvk = Usersvk::where('user_id',$data['user_id'])->first();
 if(!isset($Usersvk)){ 
  $params = array('user_ids' => $data['user_id'],'fields' => 'photo_100');
  $usersGet = VK::usersGet($this->token_moderator,$params,Null);
  $firstName = $usersGet[0]['first_name'];
  $lastName = $usersGet[0]['last_name'];
  $photo = $usersGet[0]['photo_100'];
  $Usersvk = Usersvk::create(['user_id'=>$data['user_id'],'firstname'=>$firstName,'lastname'=>$lastName,'photo'=>$photo]);
}
return $Usersvk;
}

//отображение сообщений
public function view(Request $request){

      // $result_view = array();
$c_peers = collect([]);

if(empty($request->input('group_type'))){
return redirect()->route('home');
}

      //получить диалоги
$get_peers = $this->getPeers();

      //коллекция кол-во диалогов
$c_peers->put('group_type', $request->input('group_type'));
$c_peers->put('countPeers', $get_peers['count']);
      // $result_view=['group_type'=>$request->input('group_type')];
      // $result_view=['count_peers'=>$get_peers['count']];
      // dump($c_peers);
      // dd($result_view);

foreach ($get_peers['items'] as $k_peer => $v_peer) { 
          //присвоение данных          
if($this->mode_debug) { dump($v_peer); }

          $peer_type = $v_peer['conversation']['peer']['type']; //user или ???
          
          if($peer_type=='user'){
           $a_peers[$k_peer]=$v_peer;
           $user_id = $v_peer['conversation']['peer']['id'];
              //ищем у себя этого пользователя, если нет, то добавляем
           $Usersvk = $this->getUsersInDB(['user_id'=>$user_id]);
           $this->Usersvk = $Usersvk;
           $a_peers[$k_peer]['Usersvk']=$Usersvk;

              //проверяю забанен ли юзер
           $checkUserBannedGroups = $this->checkUserBannedGroups();
           if(!empty($checkUserBannedGroups)) {
            $a_peers[$k_peer]['banUsersvk']=$checkUserBannedGroups['items'][0];    
          }

              /*array:2 [▼
              "count" => 1
              "items" => array:1 [▼
              0 => array:3 [▼
              "type" => "profile"
              "profile" => array:5 [▶]
              "ban_info" => array:6 [▼
              "admin_id" => 206862242
              "date" => 1578568492
              "reason" => 0
              "comment" => "ывавап"
              "comment_visible" => false
              "end_date" => 1581246892
            ]*/

              //dump($checkUserBannedGroups);
            

          }
          elseif($peer_type=='chat'){
           continue;
              // dd($get_peers);
              // dump('Сообщите администратору, что неизвестный peer_type');
              // //ищем у себя этого пользователя, если нет, то добавляем
              // $Usersvk = Usersvk::where('user_id',$userId)->first();
              // if(!isset($Usersvk)){ 
              //     $params = array('user_ids' => $userId,'fields' => 'photo_100');
              //     $usersGet = VK::usersGet($this->token_moderator,$params,Null);
              //     $firstName = $usersGet[0]['first_name'];
              //     $lastName = $usersGet[0]['last_name'];
              //     $photo = $usersGet[0]['photo_100'];
              //     $Usersvk = Usersvk::create(['user_id'=>$userId,'firstname'=>$firstName,'lastname'=>$lastName,'photo'=>$photo]);                    

              // }
              // $a_peers[$k_peer]['Usersvk']=$Usersvk;
         }

          //получение диалога
         $messagesGetHistory = $this->messagesGetHistory();
          //пример получения данных
          /*"count" => 3
          "items" => array:3 [▼
          0 => array:12 [▼
          "date" => 1578511093
          "from_id" => 392362811
          "id" => 10690
          "out" => 0
          "peer_id" => 392362811
          "text" => "Извините,ошиблась."
          "conversation_message_id" => 4
          "fwd_messages" => []
          "important" => false
          "random_id" => 0
          "attachments" => []
          "is_hidden" => false*/

          foreach ($messagesGetHistory['items'] as $k_history => $v_history) {

           $a_peers[$k_peer]['messages'][$k_history]=$v_history;

              //Узнаём кто автор сообщения из админов
           if(isset($v_history['admin_author_id'])){
            $fromName = "Модератор";
          }
          elseif(!isset($v_history['admin_author_id']))
          {
            $fromName = $Usersvk->firstname.' '.$Usersvk->lastname;
          }                 
          $a_peers[$k_peer]['messages'][$k_history]['from_name']=$fromName;
              //$a_peers[$k_peer]['messages'][$k_history]['admin_author_name']=$adminAuthorName;

          

              //Проверяем все вложения
          if (!empty($v_history['attachments'])) { 
            if($this->mode_debug){dump($this->checkItemsPhoto(['attachments'=>$v_history['attachments']]));}
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
                         else{dump('не найдена url для url_photo_type_min',$v_attachments['photo']['sizes']);}

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
                         elseif (isset($url_photo_type_m)) {
                           $url_photo_type_max=$url_photo_type_m;
                         }
                         elseif (isset($url_photo_type_x)) {
                           $url_photo_type_max=$url_photo_type_x;
                         }
                         else{dump('не найдена url для url_photo_type_max',$v_attachments['photo']['sizes']);}

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
               $c_peers->put('peers',$a_peers);
             }  
             if($this->mode_debug) { dump($c_peers);}              
             return view('komuche_ndm.messages.view_messages',['c_peers' => $c_peers]); 
      //dump($messagesGetHistory);

  } //end function update

//проверка вложений
  public function checkItemsPhoto($data){
   $photos = array();
   if (!empty($data['attachments'])) {                    
    foreach ($data['attachments'] as $k_attachments => $v_attachments) {
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
             else{dump('не найдена url для url_photo_type_min',$v_attachments['photo']['sizes']);}

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
             elseif (isset($url_photo_type_m)) {
               $url_photo_type_max=$url_photo_type_m;
             }
             elseif (isset($url_photo_type_x)) {
               $url_photo_type_max=$url_photo_type_x;
             }
             else{dump('не найдена url для url_photo_type_max',$v_attachments['photo']['sizes']);}

             $photos[$k_attachments]['url_photo_type_min']=$url_photo_type_min;
             $photos[$k_attachments]['url_photo_type_max']=$url_photo_type_max;
              //добавил своё представление для вывода photo
          }//type ==  photo
        }
      }
      return $photos;
    }

  //обработка полученных данных
    public function processing_message(Request $request){
      //if($this->mode_update) {dump('MessageController.processing');} 

      //получаю все команды на языке 
      $replyCommands = Lang::get('messages/replyCommands');        

      $messages=$request->input('messages');

      foreach ($messages as $vMessage) {
       $usersvkId = $vMessage['usersvk_id'];
       $userId = $vMessage['user_id'];
          //ищу этого пользователя
       $Usersvk = $this->getUsersInDB(['user_id'=>$usersvkId]);            
       $this->Usersvk = $Usersvk;

       if(!empty($vMessage['status'])){
        switch ($vMessage['status']) {
         case 'Реквизиты':
         $this->message = $replyCommands['Реквизиты'];
         $this->messagesSend(Null);                       
         break;
         case 'Прайс':
         $this->message = $replyCommands['Прайс'];                        
         $this->messagesSend(Null); 
         break;
         case 'РекламаОдобрена':
         $this->message = $replyCommands['РекламаОдобрена'];                        
         $this->messagesSend(Null); 
         break;
         case 'ПринятьВГруппу':                        
                      if(!$this->groupsisMember(Null)){ //проверяю состоит ли в группе
                          if ($this->checkRequestGroup()) { //проверяю есть ли от него заявка
                           if($this->mode_debug){dump('есть заявка - принимаем');}
                           $addUserGroup=$this->addUserGroup(Null);
                              if ($addUserGroup) { //добавляю в группу
                               if($this->mode_debug){dump('добавили');}
                               $this->message = $replyCommands['ПринятьВГруппу'];                        
                               $this->messagesSend(Null);
                             } 
                           }
                           else{
                            if($this->mode_debug){dump('нет заявки');}                                
                            $this->message = $replyCommands['НетЗаявкиВГруппе'];                        
                            $this->messagesSend(Null); 
                          }
                        }  
                        else{
                         $this->message = $replyCommands['ПринятьВГруппу'];                        
                         $this->messagesSend(Null);
                       }                        
                       break;
                       case 'Разблокировать': 
                      //проверяем заблокирован ли пользователь
                       $checkUserBannedGroups = $this->checkUserBannedGroups();
                       if(!empty($checkUserBannedGroups)) {
                          // $a_peers[$k_peer]['banUsersvk']=$checkUserBannedGroups['items'][0];                            
                          if($this->groupsUnban(Null)){ //разблокировать пользователя
                           $this->message = $replyCommands['Разблокировать'];                        
                           $this->messagesSend(Null);
                         }
                       }
                      else{ //если пользователь уже разблокирован
                       $this->message = $replyCommands['Разблокировать'];                        
                       $this->messagesSend(Null);
                     }                  
                     break; 
                     case 'ОшибкаГруппой': 
                     $this->message = $replyCommands['ОшибкаГруппой'];                        
                     $this->messagesSend(Null); 
                     break;
                     case 'ПометитьКакОтвеченную':
                     $this->messagesMarkAsAnsweredConversation(['peer_id'=>$vMessage['peer_id']]);
                     $this->messagesMarkAsRead(['peer_id'=>$vMessage['peer_id'],'start_message_id'=>$vMessage['last_message_id']]);
                     break;
                     default:
                     break;
                   }
                 }

                 if(!empty($vMessage['text_send'])){     
                   $this->message = $vMessage['text_send'];             
                   $this->messagesSend(Null);
                 } 

               }
               return redirect()->route('home'); 

             }

  //добавляем человека в группу
             public function addUserGroup($data){
               $params = array(
          'group_id' => config('vk.group_id_kndm1'), //220409092 Вячеслав Тихонов
          'user_id' => $this->Usersvk->user_id,
        );
               $groupsApproveRequest = VK::groupsApproveRequest($this->token_moderator,$params,Null); 
               return $groupsApproveRequest;
             }

  //отправка сообщения пользователю
             public function messagesSend($data){
               $params = array(             
                'user_id' => $this->Usersvk->user_id,
          'message' => $this->message, //220409092 Вячеслав Тихонов
          'random_id'=> rand(), //рандомное число
          //'group_ids' => $group_ids,    
        );
               $messagesSend = VK::messagesSend($this->token_group_kndm,$params,Null);
               return $messagesSend;     
             }

  //Проверяем состоит ли пользователь в группе true
             public function groupsisMember($data){
               $params = array(             
                'group_id' => config('vk.group_id_kndm1'),
          'user_id' => $this->Usersvk->user_id, //220409092 Вячеслав Тихонов
          //'user_ids'=> Null, //рандомное число
          //'group_ids' => $group_ids,    
        );
               $groupsisMember = VK::groupsisMember($this->token_moderator,$params,Null);
               if($this->mode_debug){dump(['$groupsisMember' => $groupsisMember]);}
               return $groupsisMember;    
             }


  //получить список заявок  в группе
             public function groupsgetRequests($data){
               $params = array(             
                'group_id' => config('vk.group_id_kndm1'),
          'offset' => $data['offset'], //смещение
          'count' =>  $data['count'], //макс 200
          'fields' => $data['fields'], //дополнительные поля

        );
               $groupsgetRequests = VK::groupsgetRequests($this->token_moderator,$params,Null);   
               return $groupsgetRequests;    
             }

  //проверяем есть ли пользователь среди всех заявок True False
             public function checkRequestGroup(){
               $offset=0; $count=0; $n=0;
      //если ниразу не создавал список, то создаю массив
               if (empty($this->requests)) { 
                if($this->mode_debug){dump(['мой список пустой']);} 
          //получить список заявок  в группе 50,200     
                $getRequests = $this->groupsgetRequests(array('offset'=>$offset,'count'=>200,'fields'=>Null)); 
                while (intval($getRequests['count']/200) >= $n) {
                 if ($n>0) {
                  sleep(1);
                  $getRequests = $this->groupsgetRequests(array('offset'=>$offset=$offset+200,'count'=>200,'fields'=>Null));
                }       
              // dump($getRequests );         
                foreach ($getRequests['items'] as $k_items => $user_id) {
                  $this->requests[]=$user_id; //собираю свой массив заявок в группу
                }            
                $n++;
              }
            }
            else{
              if($this->mode_debug){dump(['мой список уже сформирован']);} 
            }
      //сравниваю уже по ранее созданному массиву заявок 
            if($this->mode_debug){dump(['Кол-во заявок',count($this->requests)]);}        
            foreach ($this->requests as $key => $user_id) {
              if($user_id==$this->Usersvk->user_id){
               return true;
             }
           } 
           return false;  
         }

  //разблокировка пользователя в группе
         public function groupsUnban($data){
           $params = array(             
            'group_id' => config('vk.group_id_kndm1'),
            'owner_id' => $this->Usersvk->user_id,
          );
           $groupsUnban = VK::groupsUnban($this->token_moderator,$params,Null);
           return $groupsUnban;    
         }

  //Помечает беседу как отвеченую
         public function messagesMarkAsAnsweredConversation($data){
           $params = array(             
            'peer_id' => $data['peer_id'],
          'answered' => 1, //1 - беседа отмечена отвеченной, 0 - неотвеченной
          'group_id' => $this->group_id_kndm, 
        );
           $messagesMarkAsAnsweredConversation = VK::messagesMarkAsAnsweredConversation( $this->token_group_kndm,$params,Null);
           return $messagesMarkAsAnsweredConversation;    
         }

  //Помечает беседу как прочитанную
         public function messagesMarkAsRead($data){
           $params = array(             
          // 'message_ids' => $data['message_ids'],
          'peer_id' => $data['peer_id'], //
          'start_message_id' =>$data['start_message_id'], 
          'group_id'=>$this->group_id_kndm,
        );
           $messagesMarkAsRead = VK::messagesMarkAsRead( $this->token_group_kndm,$params,Null);
           return $messagesMarkAsRead;    
         }




         




}//end class
