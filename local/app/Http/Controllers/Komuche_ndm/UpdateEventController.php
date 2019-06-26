<?php
namespace Komu4e\Http\Controllers\Komuche_ndm;

set_time_limit(180);
use Illuminate\Http\Request;

use Komu4e\Http\Controllers\Controller;
use Komu4e\Http\Controllers\Komuche_ndm\PostmessageController;
use Komu4e\Http\Controllers\VK;


use Komu4e\Model\Komuche_ndm\Settings;

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
    public function updateEvent(Request $request){

        if($this->mode_debug){dump('*** UpdateEventController.updateEvent',$request->all());}        

        $token_moderator=config('vk.token_moderator');
        $group_id_kndm1=config('vk.group_id_kndm1');

        $vk = new VKApiClient(); 
        $LongPollServer = $vk->groups()->getLongPollServer($token_moderator, array( 
          'group_id' => $group_id_kndm1, 
        ));
        $last_ts['ts']=$LongPollServer['ts']; //получили настройки и последние сессии
        $Setting_last_ts = Settings::where('name','komuche_ndm_postemessage_last_ts')->first();
        //test
                
        if($this->mode_debug){dump('$komuche_ndm_postemessage_last_ts'); dump($Setting_last_ts); }
        $countNewTs = $last_ts['ts']-$Setting_last_ts->value1; //кол-во новый событий
        if($request->has('btn_update_event')){ //если обновить, то последние
            $beginTs = $last_ts['ts']-50;
        }
        else{            
            $beginTs = $last_ts['ts']-$countNewTs;    
        }     
        if($this->mode_debug){dump('Количество новых событий $countNewTs='); dump($countNewTs); }
        if($this->mode_debug){dump('$LongPollServer',$LongPollServer);Log::channel($this->log_name)->info('LongPollServer',$LongPollServer);}
        
        $handler = new CallbackApiMyHandler();          
        $executor = new VKCallbackApiLongPollExecutor($vk, $access_token=$token_moderator, $group_id=$group_id_kndm1, $handler, $wait=0);
        $result_executor = $executor->getEvents($LongPollServer['server'],$LongPollServer['key'],$beginTs); //$countNewTs получить последние 2 записи
        if($this->mode_debug){dump(['$result_executor'=>$result_executor]);}

        
        //перебираем последние события
        $PostmessageController = new PostmessageController();

        foreach ($result_executor['updates'] as $k_updates => $v_updates) {
            
            if($this->mode_debug){dump('№события=',$k_updates+1);}
            if($this->mode_debug){dump('$v_updates',$v_updates);}

            if ($v_updates['type']=='wall_post_new') { //новый пост
                if($this->log_write){Log::channel($this->log_name)->info('wall_post_new - $v_updates',$v_updates);}                
                $PostmessageController->updatePostmessage(array('k_updates' => $k_updates+1,'v_updates' => $v_updates ));
            }
            elseif($v_updates['type']=='wall_reply_new'){                
                if($this->log_write){Log::channel($this->log_name)->info('wall_reply_new - $v_updates',$v_updates);}  
                //$PostmessageController->updatePostmessage(array('k_updates' => $k_updates+1,'v_updates' => $v_updates ));                    
            }
            elseif($v_updates['type']=='wall_reply_edit'){                
                if($this->log_write){Log::channel($this->log_name)->info('wall_reply_edit - $v_updates',$v_updates);}
                //$PostmessageController->updatePostmessage(array('k_updates' => $k_updates+1,'v_updates' => $v_updates ));
            } 
            elseif($v_updates['type']=='user_unblock'){                
                if($this->log_write){Log::channel($this->log_name)->info('user_unblock - $v_updates',$v_updates);}
                //$PostmessageController->updatePostmessage(array('k_updates' => $k_updates+1,'v_updates' => $v_updates ));
            }
            elseif($v_updates['type']=='user_block'){                
                if($this->log_write){Log::channel($this->log_name)->info('user_block - $v_updates',$v_updates);}
                //$PostmessageController->updatePostmessage(array('k_updates' => $k_updates+1,'v_updates' => $v_updates ));
            }
            elseif($v_updates['type']=='wall_reply_delete'){               
                if($this->log_write){Log::channel($this->log_name)->info('wall_reply_delete - $v_updates',$v_updates);}
                //$PostmessageController->updatePostmessage(array('k_updates' => $k_updates+1,'v_updates' => $v_updates ));
            }
            elseif($v_updates['type']=='wall_reply_restore'){               
                if($this->log_write){Log::channel($this->log_name)->info('wall_reply_delete - $v_updates',$v_updates);}
                //$PostmessageController->updatePostmessage(array('k_updates' => $k_updates+1,'v_updates' => $v_updates ));
            }            
            elseif($v_updates['type']=='group_join'){               
                if($this->log_write){Log::channel($this->log_name)->info('wall_reply_delete - $v_updates',$v_updates);}
            }
            elseif($v_updates['type']=='group_leave'){               
                if($this->log_write){Log::channel($this->log_name)->info('wall_reply_delete - $v_updates',$v_updates);}
            }            
            elseif($v_updates['type']=='message_new'){               
                if($this->log_write){Log::channel($this->log_name)->info('wall_reply_delete - $v_updates',$v_updates);}
            }
            elseif($v_updates['type']=='message_reply'){               
                if($this->log_write){Log::channel($this->log_name)->info('wall_reply_delete - $v_updates',$v_updates);}
            }

            
            else{
                dump('Неизвестный тип поста $v_updates[type]',$v_updates['type']);
                Log::channel($this->log_name)->error('Неизвестный тип поста - $v_updates[type]',[$v_updates['type']]);
                Log::channel($this->log_name)->error('array $v_updates',[$v_updates]);                      
            }
        }
        $Setting_last_ts->value1=$last_ts['ts'];
        $Setting_last_ts->save();
        return redirect()->route('home');       
    } //end function update

}//end class