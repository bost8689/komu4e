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
    public $mode_debug = 1; //режим отлади //if($this->mode_debug){}
    public $log_name = 'komu4e_ndm_updatevent'; //для логирования

    //обновление ыы
    public function updateEvent(Request $request){

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

    } //end function update

}//end class