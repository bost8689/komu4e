<?php

namespace Komu4e\Http\Controllers\Komuche_ndm;

use Illuminate\Http\Request;
use Komu4e\Http\Controllers\Controller;
use Komu4e\Http\Controllers\VK;

//use Komu4e\Model\Komuche_ndm\Order;
//use Komu4e\Model\Komuche_ndm\Bnip;
//use Komu4e\Model\Komuche_ndm\Photosbnip;
use Komu4e\Model\Komuche_ndm\Usersvk;
//use Komu4e\Model\Komuche_ndm\Postmessage;
//use Komu4e\Model\Komuche_ndm\Photospostmessage;
//use Komu4e\Model\Komuche_ndm\Settings;

use Komu4e\User;
use Auth;
use Log;
use Session;

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
    public $log_write = 1; //публикация логов //if($this->log_write){}
    public $mode_debug = 1; //режим отлади //if($this->mode_debug){}
    public $log_name = 'komuche_ndm_message'; //публикация логов //if($this->log_name){} 
    private $token_moderator = Null;
    private $group_id_kndm = Null;
    private $token_group_kndm = Null;

    public function __construct(Request $request)
    {
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


	//отображение сообщений
    public function view(Request $request){

        if(empty($request->input('group_type'))){
            return redirect()->route('home');
        }

        //dd($request->all()); 
        if($this->mode_debug) {dump('MessageController.view');}         
        if($this->log_write){Log::channel($this->log_name)->info('тест лог');}
        $c_peers = collect([]);    
        //dump($this->group_id_kndm);
        //dump($this->group_id_kndm);

        $params = array(
        'offset' => 0,
        'count' => 20, //по умолчанию 20, мах 200
        'filter' => 'unread',
        // all — все беседы; 
        // unread — беседы с непрочитанными сообщениями;
        // important — беседы, помеченные как важные (только для сообщений сообществ);
        // unanswered — беседы, помеченные как неотвеченные (только для сообщений сообществ). 
        'extended' => 1,//1 — возвращать дополнительные поля для пользователей и сообществ. флаг, может принимать значения 1 или 0.
        'start_message_id' => 5000,
        'group_id' => $this->group_id_kndm,
        'fields' => 'name,ban_info',        
        );
        $messagesGetConversations = VK::messagesGetConversations($this->token_group_kndm,$params,Null);
        //коллекция кол-во диалогов
        $c_peers->put('group_type', $request->input('group_type'));
        $c_peers->put('countPeers', $messagesGetConversations['count']);
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
                'group_id' => config('vk.group_id_kndm1'),
                'offset' => 0,
                'count' => 1,// 
                'fields' => 0,//1 – возвращать сообщения в хронологическом порядке. 0 – возвращать сообщения в обратном хронологическом порядке (по умолчанию).
                'owner_id' => $userId,   
                );

                $getBanned = VK::groupsGetBanned(config('vk.token_group_kndm1'),$params,Null);

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
        if($this->mode_debug) { dump($c_peers); }         
        return view('komuche_ndm.messages.view_messages',['c_peers' => $c_peers]); 
        //dump($messagesGetHistory);

    } //end function update

    //обработка полученных данных
    public function processing_message(Request $request){
        //if($this->mode_update) {dump('MessageController.processing');}  

        //dd($request->input());
        $messages=$request->input('messages');

        foreach ($messages as $vMessage) {
            $usersvkId = $vMessage['usersvk_id'];
            $userId = $vMessage['user_id'];
            //ищу этого пользователя
            $Usersvk = Usersvk::find($usersvkId);
            if(!isset($Usersvk)){ 
                $params = array('user_ids' => $userId,'fields' => 'photo_100');
                $usersGet = VK::usersGet($this->token_moderator,$params,Null);
                $firstName = $usersGet[0]['first_name'];
                $lastName = $usersGet[0]['last_name'];
                $photo = $usersGet[0]['photo_100'];
                $Usersvk = Usersvk::create(['user_id'=>$userId,'firstname'=>$firstName,'lastname'=>$lastName,'photo'=>$photo]);
            }

            if(!empty($vMessage['status'])){
                switch ($vMessage['status']) {
                    case 'Реквизиты':
                        $message = '1. Перед каждой оплатой уточняйте реквизиты.                        
                        2. В комментариях платежа ничего писать не надо.
                        3. Для 2 типа размещения (постов от своего имени: ) После оплаты ожидайте от нас ответа, что Вас добавили в программу и только после этого можно будет размещать информацию, иначе программа заблокирует Вас.

                        Газпромбанк 5264 8321 2715 3548 
                        Сбербанк 5336 6900 1286 3579
                        Яндекс деньги 4100 134 9075 1099

                        4. После оплаты напишите, что отправили: сумму, на какую карту отправили и Имя отправителя.';
                        $this->send_message(array('message' => $message),$Usersvk);                       
                        break;
                    case 'Прайс':
                        $message = 'Информационный пост в сообществе "КомуЧё Объявления Надым" в социальной сети ВКонтакте, vk.com/komuche 

                        Типы размещения:
                        1. Закрепление записи в шапке группы. 1200 руб. на сутки. Никаких ссылок (ссылку можно только в комментариях под записью), только текст и картинка. Запись видна всем и в закреплении будет одна единственная ваша запись. Закрепление записи происходит в Прайм-тайм с 21:00 до 22:00.
                        Оплату и информацию для закрепа просим предоставлять за 3 дня до размещения. Если материал и оплата не поступила за 3 дня мы в праве отменить бронирование. 

                        2. Размещение одного информационного поста (с интервалом 1 сообщение в сутки) на стене от вашего имени, размещаете лично в удобное для вас время = 100 руб. Размещение не более одного раза в сутки. 

                        3. Размещение одного рекламного поста на стене от имени группы в любое время (выбираете лично) = 390 руб. 

                        4. Размещение визитки в фотоальбоме "Визитки" который будет виден при прямом просмотре группы В КомуЧё объявления и через меню группы. Стоимость размещения на один месяц 300 рублей. 

                        Как сделать заказ? 
                        1. После выбора типа размещения, высылаете номер типа размещения и вашу информацию, которую будете размещать для согласования. Запрещено размещать рекламу алкоголя, табака, информацию сексуального характера, а так же нарушающую ФЗ "О рекламе" и правила "ВКонтакте"
                        2. После согласования информации Вам высылают реквизиты на оплату. 
                        Способы оплаты удобным для Вас способом: 
                        Перевод денежных средств на карту Сбербанк, Газпромбанк, Yandex деньги. 

                        Дополнительные наши услуги: 
                        1. Оформление групп, а так же их ведение. 
                        5. Услуги автоматизации обработки информации в социальных группах. Интернет-магазинов. 

                        С Уважением команда КомуЧё';
                        $this->send_message(array('message' => $message),$Usersvk); 
                        break;
                    case 'ПринятьВГруппу':
                        //code принять в группу 

                        // $message = 'ПринятьВГруппу';
                        // $this->send_message(array('message' => $message),$Usersvk); 
                        break;
                    case 'Разблокировать': 
                        //code Разблокировать                       
                        // $message = 'Разблокировали';
                        // $this->send_message(array('message' => $message),$Usersvk);
                        break; 
                    case 'ОшибкаГруппой': 
                        //code Разблокировать                       
                        $message = 'Здравствуйте, вы написали к нам в сообщения сообщества , объявления размещать надо на стене в сообществе КомуЧё Объявления - https://vk.com/komuche.
                        С Уважением команда КомуЧё 
                        Наши сообщества: 
                        КомуЧё Надым - городское сообщество. vk.com/komuche_nadym 
                        КомуЧё Авто - попутчики, грузо и пассажироперевозки 
                        https://vk.com/auto_nadym 
                        КомуЧё Потеряшка - бюро находок и потерь https://vk.com/bnip_nadym 
                        КомуЧё Мамочки 
                        https://vk.com/komuche_mamomochki 
                        КомуЧё Объявления - https://vk.com/komuche';
                        $this->send_message(array('message' => $message),$Usersvk); 
                        break;                     
                    default:
                        dump([$vMessage,'status'=>$vMessage['status'],'неизвестный status']);
                        break;
                }
                dump([$vMessage,'status'=>$vMessage['status'],'']);
            }
            else{
                //dump([$vMessage,$vMessage['status'],'Статус empty']);
            }

            if(!empty($vMessage['text_send'])){                
                $this->send_message(array('message' => $vMessage['text_send']),$Usersvk);
            } 

        }
        return redirect()->route('home'); 

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
