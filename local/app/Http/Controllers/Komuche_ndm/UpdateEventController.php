<?php
namespace Komu4e\Http\Controllers\Komuche_ndm;

set_time_limit(180);
use Illuminate\Http\Request;

//контроллеры
use Komu4e\Http\Controllers\Controller;
use Komu4e\Http\Controllers\Komuche_ndm\PostmessageController; 
use Komu4e\Http\Controllers\VK;

//БД 
use Komu4e\Model\Komuche_ndm\Settings;

//дополнительное подключени
use Komu4e\User;
use Auth;
use Log;
use Session;

use VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor;
use VK\CallbackApi\VKCallbackApiHandler;
use VK\Client\VKApiClient;
use VK\Client\VKApiRequest;
use VK\Exceptions\VKApiException;



class CallbackApiMyHandler extends VKCallbackApiHandler { 
   
}       

class UpdateEventController extends Controller
{	
    public $log_write = 0; //публикация логов //if($this->log_write){}
    public $mode_debug = 0; //режим отлади //if($this->mode_debug){}
    public $log_name = 'komu4e_ndm_updatevent'; //для логирования

    //обновление ыы
    /*public function updateEvent(Request $request){

        //включить отладку или нет
        if (!empty($request->input('debug') )) {
            $this->mode_debug=1;
        }

        $token_moderator=config('vk.token_moderator');
        $group_id_kndm1=config('vk.group_id_kndm1');

        //Получение новых событий
        $vk = new VKApiClient(); 
        $LongPollServer = $vk->groups()->getLongPollServer($token_moderator, array( 
          'group_id' => $group_id_kndm1, 
        ));
        $lastTs=$LongPollServer['ts']; //получили настройки и последние сессии
        $SettingLastTs = Settings::where('name','komu4e_ndm_postmessage_last_ts')->first();
        $settingBeginTs = $SettingLastTs->value1; //на начало было
        $countNewTs = $lastTs-$SettingLastTs->value1; //кол-во новый событий

        //Если долго не модерировалась - для обновления
        if($request->has('btn_update_event')){ //если обновить, то последние
            $beginTs = $lastTs-50;
        }
        else{            
            $beginTs = $lastTs-$countNewTs;    
        }   
        
        //Получение настроек и событий от ВК
        $handler = new CallbackApiMyHandler();          
        $executor = new VKCallbackApiLongPollExecutor($vk, $access_token=$token_moderator, $group_id=$group_id_kndm1, $handler, $wait=0);
        $result_executor = $executor->getEvents($LongPollServer['server'],$LongPollServer['key'],$beginTs);         

        //перебираем последние события
        //Формирую массивы событий по их типу
        $wallPostNew = array();
        $wallReplyNew = array();
        $wallReplyEdit = array();
        foreach ($result_executor['updates'] as $k_updates => $v_updates) {
            switch ($v_updates['type']) {
                case 'wall_post_new':
                    $wallPostNew[]=$v_updates;
                    break;
                case 'wall_reply_new':
                    $wallReplyNew[]=$v_updates;
                    break;
                case 'wall_reply_edit':
                    $wallReplyEdit[]=$v_updates;
                    break;                
                default:
                    //dump('Неизвестный тип поста $v_updates[type]',$v_updates['type']);
                    //Log::channel($this->log_name)->error('Неизвестный тип поста - $v_updates[type]',[$v_updates['type']]);
                    break;
            }            
        }    

        //Обновляю последнюю запись события в БД
        $SettingLastTs->value1=$lastTs;
        $SettingLastTs->save();        

        //Отладка
        if($this->mode_debug){dump([
            'Отладка'=>'UpdateEventController.updateEvent',
            'Последнее число событий в БД на начало $settingBeginTs'=>$settingBeginTs,
            'Последнее число событий в ВК $lastTs'=>$lastTs,
            'Кол-во новыйх событий $countNewTs'=>$countNewTs,
            'Настройки сервера LongPollServer'=>$LongPollServer,
            'События $result_executor'=>$result_executor,
            'Кол-во новых $wallPostNew'=>count($wallPostNew),
            'Последнее число событий в БД на конец SettingLastTs'=>$SettingLastTs->value1,
        ]);}    

        //Запускаю функцию записи новых постов
        if (!empty($wallPostNew)) {
            $PostmessageController = new PostmessageController(); 
            $PostmessageController->writeWallPostNew($wallPostNew);
        }          

        //return redirect()->route('home');
    } //end function update*/

    public function getEvent(){
        //включить отладку или нет
        $token_moderator=config('vk.token_moderator');
        $group_id_kndm1=config('vk.group_id_kndm1');

        //Получение новых событий
        $vk = new VKApiClient(); 
        $LongPollServer = $vk->groups()->getLongPollServer($token_moderator, array( 
          'group_id' => $group_id_kndm1, 
        ));
        $lastTs=$LongPollServer['ts']; //получили настройки и последние сессии
        $SettingLastTs = Settings::where('name','komu4e_ndm_postmessage_last_ts')->first();
        $settingBeginTs = $SettingLastTs->value1; //на начало было
        $countNewTs = $lastTs-$SettingLastTs->value1; //кол-во новый событий

        //Номера начальных событий
        $beginTs = $lastTs-$countNewTs;
        //dump($lastTs);
        //Получение настроек и событий от ВК
        $handler = new CallbackApiMyHandler();          
        $executor = new VKCallbackApiLongPollExecutor($vk, $access_token=$token_moderator, $group_id=$group_id_kndm1, $handler, $wait=0);
        $result_executor = $executor->getEvents($LongPollServer['server'],$LongPollServer['key'],$beginTs);         
        if($this->mode_debug){dump($result_executor);}

/*              "type" => "wall_post_new"
      "object" => array:11 [▼
        "id" => 1887562
        "from_id" => 220409092
        "owner_id" => -46590816
        "date" => 1578755679
        "marked_as_ads" => 0
        "post_type" => "post"
        "text" => "Продам гитару , обычная 6 струнная за 5000 руб."
        "can_edit" => 1
        "created_by" => 220409092
        "can_delete" => 1
        "comments" => array:1 [▶]
      ]
      "group_id" => 46590816
      "event_id" => "7c3bdffb8471106dd3b04d3d4537c2713641f2c6"*/
        //перебираем последние события
        //Формирую массивы событий по их типу
        $wallPostNew = array();
        $wallReplyNew = array();
        $wallReplyEdit = array();
        foreach ($result_executor['updates'] as $k_updates => $v_updates) {
            switch ($v_updates['type']) {
                case 'wall_post_new':
                    $wallPostNew[]=$v_updates;
                    break;
                case 'wall_reply_new':
                    $wallReplyNew[]=$v_updates;
                    break;
                case 'wall_reply_edit':
                    $wallReplyEdit[]=$v_updates;
                    break;                
                default:
                    break;
            }            
        }    

        //Обновляю последнюю запись события в БД
        $SettingLastTs->value1=$lastTs;
        $SettingLastTs->save();   

        //Запускаю функцию записи новых постов
        if (!empty($wallPostNew)) {
            $PostmessageController = new PostmessageController(); 
            $PostmessageController->writeWallPostNew($wallPostNew);
        }

        return array(            
            'Последнее число событий в БД на начало $settingBeginTs'=>$settingBeginTs,
            'Последнее число событий в ВК $lastTs'=>$lastTs,
            'Кол-во новыйх событий $countNewTs'=>$countNewTs,
            'Кол-во новых $wallPostNew'=>count($wallPostNew),
        );      
    }

}//end class