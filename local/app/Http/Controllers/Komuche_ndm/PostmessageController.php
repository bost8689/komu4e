<?php
namespace Komu4e\Http\Controllers\Komuche_ndm;

set_time_limit(720);
use Illuminate\Http\Request;
use Komu4e\Http\Controllers\Controller;
use Komu4e\Http\Controllers\Komuche_ndm\UpdateEventController;
use Komu4e\Http\Controllers\VK;

use Komu4e\Model\Komuche_ndm\Order;
use Komu4e\Model\Komuche_ndm\Bnip;
use Komu4e\Model\Komuche_ndm\Photosbnip;
use Komu4e\Model\Komuche_ndm\Usersvk;
use Komu4e\Model\Komuche_ndm\Postmessage;
use Komu4e\Model\Komuche_ndm\Photospostmessage;
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

class PostmessageController extends Controller
{	
    //public $mode_update = 1; //обновление перед показом включено //if($this->mode_update){}
    public $log_write = 1; //публикация логов //if($this->log_write){}
    public $mode_debug = 0; //режим отлади //if($this->mode_debug){}
    public $log_name = 'komuche_ndm_postmessage'; //публикация логов //if($this->log_name){}
    private $group_id_kndm1 = Null; //публикация логов //if($this->log_name){}
    private $token_moderator = Null;
    public $mode_banUsersvk = 1; //включена блокировка
    
    public function __construct()
    {
        $this->token_moderator=config('vk.token_moderator');
        $this->group_id_kndm1=config('vk.group_id_kndm1');        
    }

	//отображение постов
    public function view(Request $request){ 

    	//включить отладку или нет
        if (!empty($request->input('debug') )) {
            $this->mode_debug=1;
        }

        //присвоение данных
        $date_view_wall = $request->input('date_view_wall');
        $checkbox_all = $request->input('checkbox_all');
        $checkbox_status_is = $request->input('checkbox_status_is'); //если есть статус

        //если это не показать выполненные то показывается обновление
        // if(!isset($checkbox_status_is)){
        //     //обновление
        //     $UpdateEventController = new UpdateEventController();
        //     $UpdateEventController->updateEvent($request);
        // }    

        $PostmessagesYesterday=Postmessage::whereNull('status')->where('date','<', $date_view_wall.' 00:00:00')->orderBy('date', 'desc')->get();
        // dd(count($PostmessagesYesterday));

        //Получаю объект постов для отображения с условиями
        if (isset($checkbox_all)) { //если хочу показать (все - обработанные и не обработанные), независимо от статуса
            $Postmessages=Postmessage::with('usersvk')->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();
        }//если хочу показать (обработанные), то только которые содержат статус
        elseif(isset($checkbox_status_is)){$Postmessages=Postmessage::with('usersvk')->whereNotNull('status')->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();
        }
        else{ //если хочу показать (не обработанные), которые не содержать статус
            $Postmessages=Postmessage::with('usersvk')->whereNull('status')->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();
        }

        //создаю коллекцию уникальных юзеров для последующей группировки
        $collectUsersvk = collect([]);
        foreach ($Postmessages as $k_Postmessage => $Postmessage) {
            $collectUsersvk->push(['id' => $Postmessage->usersvk->id,'user_id' => $Postmessage->usersvk->user_id]);
        }
        $collectUsersvk = $collectUsersvk->unique('user_id');          

        //создаю коллекцию постов по уникальным полльзователея
        $collectPostmessages = collect([]);
        foreach ($collectUsersvk as $k_usersvk => $v_usersvk) {
            if (isset($checkbox_all)) { //если хочу показать (все - обработанные и не обработанные), независимо от статуса
                $Postmessages=Postmessage::with('photospostmessage','user')->where('usersvk_id',$v_usersvk['id'])->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();
            }
            elseif (isset($checkbox_status_is)) { //если хочу показать (обработанные), то только которые содержат статус
                $Postmessages=Postmessage::with('photospostmessage','user')->where('usersvk_id',$v_usersvk['id'])->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();
            }
            else{ //если хочу показать (не обработанные), которые не содержать статус
                $Postmessages=Postmessage::with('photospostmessage','user')->where('usersvk_id',$v_usersvk['id'])->where('status',Null)->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();
            }
            $collectPostmessages->push(['Usersvk' => Usersvk::where('id','=',$v_usersvk['id'])->first(),'Orders' => Order::where('usersvk_id','=',$v_usersvk['id'])->where('status','=',Null)->get(),'Postmessages' => $Postmessages]);
        }

        //Отладка
        if($this->mode_debug){dump([
            'Отладка'=>'PostmessageController.view',            
            'Входящие данные $request->all()'=>$request->all(),    
            'кол-во уникальных пользователей $collectUsersvk'=>count($collectUsersvk),        
            'коллекция уникальных пользователей $collectUsersvk'=>$collectUsersvk,
            'коллекция постов по уникальным пользователям $collectPostmessages'=>$collectPostmessages,
        ]);}  


        //счётчик добавленных
        $lastCountAddDBPostmessage = Settings::where('name','komu4e_ndm_lastCountAddDBPostmessage')->first();
        $lastCountAddDBUsersvk = Settings::where('name','komu4e_ndm_lastCountAddDBUsersvk')->first();        
        $lastCountAddDBPhotosPost = Settings::where('name','komu4e_ndm_lastCountAddDBPhotosPost')->first();       

        return view('komuche_ndm.postmessage.view_postmessage',['collectPostmessages' => $collectPostmessages,'PostmessagesYesterday'=>$PostmessagesYesterday,'lastCountAddDBPostmessage'=>$lastCountAddDBPostmessage->value1,'lastCountAddDBUsersvk'=>$lastCountAddDBUsersvk->value1,'lastCountAddDBPhotosPost'=>$lastCountAddDBPhotosPost->value1]); 
    } //end function view

    //обновление
    public function writeWallPostNew(array $wallPostNews){ //входят обновления updates из UpdateEventController
            
            //объявление пременных
         //    $token_moderator=config('vk.token_moderator');
        	// $group_id_kndm1=config('vk.group_id_kndm1');  
        	$countAddDBPostmessage = 0;
        	$countAddDBUsersvk = 0;
            $countAddDBPhotosPost = 0;

            //перебираю посты
            foreach ($wallPostNews as $wallPostNew) {
                if ($wallPostNew['type']=='wall_post_new') { //новый пост
                    //проверяем есть ли такой пост у меня в базе                
                    //если нет заносим в базу данных             
                    $Postmessage = Postmessage::where('source_id','=',$wallPostNew['object']['id'])->first();
                    if (empty($Postmessage)) {
                    		$userId = $wallPostNew['object']['from_id'];
                    		if ($wallPostNew['object']['from_id']>0) {
                    			$params = array('user_ids' => $userId,'fields' => 'photo_100');
                    		    $usersGet = VK::usersGet($this->token_moderator,$params,Null);                		    
                    		    $firstName = $usersGet[0]['first_name'];
                    		    $lastName = $usersGet[0]['last_name'];
                    		    $photo = $usersGet[0]['photo_100'];
                    		} 
                    		else{
                    			$params = array('group_id' => abs($wallPostNew['object']['from_id']),'fields' => '');
                    			$groupsGetById = VK::groupsGetById($this->token_moderator,$params,Null);   
                    			dump($groupsGetById);       
                       			$firstName = $groupsGetById[0]['name'];              
                       			$lastName = Null;
                       			$photo = $groupsGetById[0]['photo_100'];
                    		}
                            //поиск и добавление нового пользователя ВК
                            $Usersvk = Usersvk::where('user_id',$userId)->first();
                            if(!isset($Usersvk)){ //если нет такого пользователя, то создаём
                            	$Usersvk = Usersvk::create(['user_id'=>$userId,'firstname'=>$firstName,'lastname'=>$lastName,'photo'=>$photo]);
                            	$countAddDBUsersvk++;
                            }                        	
                            else{ //если есть такой пользователь, то обновляем фото
                                $Usersvk->photo = $photo;
                                $Usersvk->save();
                            }       

                        // Если сработал робот
                        if (empty(Auth::user()->id)) {
                            $userId = 1; //администратор
                        }
                        else{
                            $userId = Auth::user()->id;   
                        }
                        //создаю новый объект в БД
                        $Postmessage = Postmessage::create(['source_id'=>$wallPostNew['object']['id'],'usersvk_id'=>$Usersvk->id,'text'=>$wallPostNew['object']['text'],'date'=>date('Y-m-d H:m:s',$wallPostNew['object']['date']),'user_id'=>$userId,'status'=>Null,'type_status'=>Null]);
                        $countAddDBPostmessage++;

                        //перебираю фото этого поста
                        if (isset($wallPostNew['object']['attachments'])){                        
                            foreach ($wallPostNew['object']['attachments'] as $k_attachment => $v_attachment) {
                                if($v_attachment['type']=='photo'){                              
                                    $url_photo_type_max=Null;
                                    $url_photo_type_min=Null;
                                    $url_photo_type_s=Null;
                                    $url_photo_type_m=Null;
                                    $url_photo_type_x=Null;
                                    $url_photo_type_y=Null;
                                    $url_photo_type_z=Null;
                                    $url_photo_type_w=Null;
                                    $url_photo_type_o=Null;
                                    $url_photo_type_p=Null;
                                    $url_photo_type_q=Null;
                                    $url_photo_type_r=Null;                                
                                    foreach ($v_attachment['photo']['sizes'] as $k_sizes => $v_sizes) {
                                        //dump($v_sizes);//m,x,y,z,w
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
                                        elseif($v_sizes['type']=='w'){
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
                                            //Log::channel($this->log_name)->error('неизвестный тип для фото $wallPostNew',$wallPostNew);
                                            dump('неизвестный type photo $wallPostNew',$wallPostNew);
                                        }
                                    }
                                    //проверяю на наличие url ссылки 
                                    if(isset($url_photo_type_m)){$url_photo_type_min=$url_photo_type_m;}
                                        elseif (isset($url_photo_type_x)) {$url_photo_type_min=$url_photo_type_x;}
                                        elseif (isset($url_photo_type_p)) {$url_photo_type_min=$url_photo_type_p;}
                                        elseif (isset($url_photo_type_q)) {$url_photo_type_min=$url_photo_type_q;}
                                        elseif (isset($url_photo_type_r)) {$url_photo_type_min=$url_photo_type_r;}
                                        elseif (isset($url_photo_type_y)) {$url_photo_type_min=$url_photo_type_y;}
                                        else{dump('не найдена url для url_photo_type_min',$v_attachment);
                                        //Log::channel($this->log_name)->error('не найдена url для url_photo_type_min',$v_attachment);
                                    }

                                    if(isset($url_photo_type_y)){
                                        $url_photo_type_max=$url_photo_type_y;}
                                    elseif (isset($url_photo_type_z)) {$url_photo_type_max=$url_photo_type_z;}
                                    elseif (isset($url_photo_type_w)) {$url_photo_type_max=$url_photo_type_w;}
                                    elseif (isset($url_photo_type_o)) {$url_photo_type_max=$url_photo_type_o;}
                                    elseif (isset($url_photo_type_m)) {$url_photo_type_max=$url_photo_type_m;}
                                    elseif (isset($url_photo_type_x)) {$url_photo_type_max=$url_photo_type_x;}
                                    else{dump('не найдена url для url_photo_type_max',$v_attachment);
                                    //Log::channel($this->log_name)->error('не найдена url для url_photo_type_max',$v_attachment);
                                    }
                                    //записываю новую фотку
                                    $Photospostmessage = Photospostmessage::create(['filenamemax'=>Null,'pathmax'=>Null,'typemax'=>Null,'photomax_url'=>$url_photo_type_max,'filenamemin'=>Null,'pathmin'=>Null,'typemin'=>Null,'photomin_url'=>$url_photo_type_min,'postmessage_id'=>$Postmessage->id]);
                                    $countAddDBPhotosPost++;
                                }                            
                            }  
                        }
                    }
                }
            }
            // $resultWallPostNew['countAddDBPostmessage'] = $countAddDBPostmessage;
            // $resultWallPostNew['countAddDBUsersvk'] = $countAddDBUsersvk;

            //Отладка
            if($this->mode_debug){dump([
                'Отладка'=>'PostmessageController.writeWallPostNew',                        
                'Входящие данные $wallPostNeWs'=>$wallPostNews,
                'Кол-во новых записанных постов countAddDBPostmessage'=>$countAddDBPostmessage,
                'Кол-во новых записанных полльзователей countAddDBUsersvk'=>$countAddDBUsersvk,
                'Кол-во новых записанных фотографий countAddDBPhotosPost'=>$countAddDBPhotosPost,
            ]);}

            //счётчик добавленных
            $lastCountAddDBPostmessage = Settings::where('name','komu4e_ndm_lastCountAddDBPostmessage')->first();
            $lastCountAddDBPostmessage->value1 = $lastCountAddDBPostmessage->value1 + $countAddDBPostmessage;
            $lastCountAddDBPostmessage->save();
            $lastCountAddDBUsersvk = Settings::where('name','komu4e_ndm_lastCountAddDBUsersvk')->first();
            $lastCountAddDBUsersvk->value1 = $lastCountAddDBUsersvk->value1 + $countAddDBUsersvk;
            $lastCountAddDBUsersvk->save();
            $lastCountAddDBPhotosPost = Settings::where('name','komu4e_ndm_lastCountAddDBPhotosPost')->first();
            $lastCountAddDBPhotosPost->value1 = $lastCountAddDBPhotosPost->value1 + $countAddDBPhotosPost;
            $lastCountAddDBPhotosPost->save();

            return array('Отладка'=>'PostmessageController.writeWallPostNew',                        
                'Входящие данные $wallPostNeWs'=>$wallPostNews,
                'Кол-во новых записанных постов countAddDBPostmessage'=>$countAddDBPostmessage,
                'Кол-во новых записанных полльзователей countAddDBUsersvk'=>$countAddDBUsersvk,
                'Кол-во новых записанных фотографий countAddDBPhotosPost'=>$countAddDBPhotosPost);
            // return $resultWallPostNew;
    } //end function update

    //обработка полученных данных
    public function processing(Request $request){

        //счётчик выполненных комманд
        $countPostCommand=array('Удалить' => 0,'Реклама' => 0,'Повтор' => 0,'Ссылка' => 0,'Более3' => 0,'Найдено' => 0,'Потеряно' => 0,'Заказ' => 0,'Просмотрено'=>0);    
        
        //перебираю полученные данные
        foreach ($request->input('processing') as $k_processing => $v_processing) {
            $Usersvk = Usersvk::find($v_processing['usersvk_id']);

            foreach ($v_processing['postmessage_id'] as $postmessage_id => $command) {
                $Postmessage = Postmessage::with('photospostmessage')->find($postmessage_id); //Нахожу по id этот пост у себя в БД
                if ($command=='Удалить') {   
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command)); 
                    $Postmessage->status=$command;
                    $countPostCommand[$command]++;
                }
                elseif($command=='Реклама'){  
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);                  
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command));
                    if($this->mode_banUsersvk){ $this->banUsersvk(array('command'=>$command),$Usersvk); }
                    $Postmessage->status=$command;
                    $countPostCommand[$command]++;                                     
                }
                elseif($command=='Повтор'){
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command));
                    if($this->mode_banUsersvk){ $this->banUsersvk(array('command'=>$command),$Usersvk); }
                    $Postmessage->status=$command;
                    $countPostCommand[$command]++;
                }
                elseif($command=='Ссылка'){
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command));
                    if($this->mode_banUsersvk){ $this->banUsersvk(array('command'=>$command),$Usersvk); } 
                    $Postmessage->status=$command;
                    $countPostCommand[$command]++;
                    //сохраняю фотографии 
                }
                elseif($command=='Более3'){
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);                    
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command));
                    if($this->mode_banUsersvk){ $this->banUsersvk(array('command'=>$command),$Usersvk); }
                    $Postmessage->status=$command; 
                    $countPostCommand[$command]++;                   
                }
                elseif($command=='Найдено'){                    
                    $Postmessage->status=$command;
                    $Bnip = Bnip::create(['source_id'=>Null,'type_source'=>'group','post_id'=>Null,'type_post'=>Null,'usersvk_id'=>$Usersvk->id,'text'=>$Postmessage->text,'user_id'=>Auth::user()->id,'status'=>$command,'type_status'=>Null]);
                    $this->savePhoto(array('type'=>$command,'Bnip'=>$Bnip),$Postmessage);
                    $countPostCommand[$command]++;
                }
                elseif($command=='Потеряно'){                    
                    $Postmessage->status=$command;
                    $Bnip = Bnip::create(['source_id'=>Null,'type_source'=>'group','post_id'=>Null,'type_post'=>Null,'usersvk_id'=>$Usersvk->id,'text'=>$Postmessage->text,'user_id'=>Auth::user()->id,'status'=>$command,'type_status'=>Null]);
                    $this->savePhoto(array('type'=>$command,'Bnip'=>$Bnip),$Postmessage);
                    $countPostCommand[$command]++;
                }
                elseif($command=='Заказ'){  
                    //кол-во заказов
                    $count_orders = count($v_processing['Orders']);
                    foreach ($v_processing['Orders'] as $k_order => $v_order) {                        
                        if($k_order==$count_orders-1) { //если это последний заказ
                            $Order = Order::where('id',$v_order['id'])->first();
                            //if($this->mode_debug){dump('заказ до',$Order);}
                            $Order->executed=$Order->executed+1;
                            if($Order->executed>=$Order->ordered){
                                $Order->status='Выполнен';                                
                            }                            
                            $Order->save();                            
                            //if($this->mode_debug){dump('заказ после',$Order);}
                        }                      
                    }                    
                    $Postmessage->status=$command;
                    $Postmessage->type_status=$Order->id;
                    $this->savePhoto(array('type'=>$command),$Postmessage);
                    $countPostCommand[$command]++;                    
                }
                else {
                    //стоит галочка просмотрено
                    if (!empty($request->input('checkbox_prosmotreno'))) {
                        if ($Postmessage->status==Null) { //чтобы стараые команды не перезаписывались
                            $Postmessage->status='Просмотрено';
                            $countPostCommand['Просмотрено']++;
                        }                            
                    }
                }

                //сохраняю все изменения
                $Postmessage->user_id=Auth::user()->id; 
                $Postmessage->save();
            }
        }

        //Отладка
        if($this->mode_debug){dump([
            'Отладка'=>'PostmessageController.processing',            
            'Входящие данные $request->all()'=>$request->all(),
            'Входящие данные $request->input(processing)'=>$request->input('processing'),
            'Кол-во выполненных команд $countPostCommand'=>$countPostCommand,
        ]);}

        //обнуляю счётчик добавленных постов
        $lastCountAddDBPostmessage = Settings::where('name','komu4e_ndm_lastCountAddDBPostmessage')->first();
        $lastCountAddDBPostmessage->value1 = 0;
        $lastCountAddDBPostmessage->save();
        $lastCountAddDBUsersvk = Settings::where('name','komu4e_ndm_lastCountAddDBUsersvk')->first();
        $lastCountAddDBUsersvk->value1 = 0;
        $lastCountAddDBUsersvk->save();
        $lastCountAddDBPhotosPost = Settings::where('name','komu4e_ndm_lastCountAddDBPhotosPost')->first();
        $lastCountAddDBPhotosPost->value1 = 0;
        $lastCountAddDBPhotosPost->save();

        return view('komuche_ndm.postmessage.processing_postmessage');
    }

    //сохранения фотографий
    public function savePhoto(array $arrData,$Postmessage){
        $debug['Отладка']='PostmessageController.savePhoto';

        foreach ($Postmessage->photospostmessage as $k_photo => $Photo) {
            $typeMax = 'jpg';
            $fileNameMax = $Postmessage->id.'_'.$k_photo.'.'.$typeMax;
            if ($arrData['type']=='postmessage') {
                $pathMax = 'public/komu4e_ndm/postmessage/';
                $img =Image::make($Photo->photomax_url)->save($pathMax.$fileNameMax);
            }
            elseif($arrData['type']=='Найдено'){
                $pathMax = 'public/komu4e_ndm/bnip/naideno/';
                $Bnip = $arrData['Bnip'];
                $Photosbnip = Photosbnip::create(['filenamemax'=>$fileNameMax,'pathmax'=>$pathMax,'bnip_id'=>$Bnip->id]);
                $img =Image::make($Photo->photomax_url);
                // $debug[$Bnip->id]['Найдено - $img->height']=$img->height();
                // $debug[$Bnip->id]['Найдено - $img->width']=$img->width();                
                $img->insert('public/bnipWatermark.png', 'bottom-right');
                $img->insert('public/bnipWatermark2.png', 'center')->save($pathMax.$fileNameMax);

            }
            elseif($arrData['type']=='Потеряно'){
                $pathMax = 'public/komu4e_ndm/bnip/poteryano/';
                $Bnip = $arrData['Bnip'];
                $Photosbnip = Photosbnip::create(['filenamemax'=>$fileNameMax,'pathmax'=>$pathMax,'bnip_id'=>$Bnip->id]);
                $img->insert('public/bnipWatermark.png', 'bottom-right');
                $img->insert('public/bnipWatermark2.png', 'center')->save($pathMax.$fileNameMax);
            }
            elseif($arrData['type']=='Заказ'){
                $pathMax = 'public/komu4e_ndm/zakaz/';
                $img =Image::make($Photo->photomax_url)->save($pathMax.$fileNameMax);
            }
            else{
                dump('Неизвестный тип $pathMax сообщите администратору, сделайте скрин этой ошибки.'.$pathMax);
                return;
            }            
            $Photo->filenamemax = $fileNameMax;
            $Photo->pathmax = $pathMax;
            $Photo->typemax = $typeMax;
            $Photo->save();            
        }
        if($this->mode_debug){dump($debug);}
        return;   
    }

    //Удаление поста и запись 
    public function deletePostmessage(array $arrData){        
        $params = array ('owner_id' => -1 * $this->group_id_kndm1,'post_id' =>  $arrData['post_id'] );
        $wall_delete = VK::wall_delete($this->token_moderator,$params,array('name'=>'wall_delete','log_name' => $this->log_name));
        // $Postmessage->status=$arrData['status'];                    
        // $Postmessage->save(); 
        return $wall_delete;   
    }

    //блокировка пользователя за рекламу
    public function banUsersvk(array $arrData,$Usersvk){

        if ($arrData['command']=='Реклама') {
            $comment='Нарушение правил группы КомуЧё Надым. Коммерческое сообщение на стене без согласования администратором. На стене реклама платная. Читайте правила и ответы на часто задаваемые вопросы. С Уважением КомуЧё Надым';
            $end_date = time()+1209600;
        }
        elseif ($arrData['command']=='Повтор')
        {
            $comment='Нарушение правил группы КомуЧё Надым. Повторные (по смыслу, тексту, фото) сообщения за период в течении суток .Читайте правила и ответы на часто задаваемые вопросы. С Уважением КомуЧё Надым';
            $end_date = time()+604800; //604800 1 неделя
        }
        elseif ($arrData['command']=='Более3')
        {
            $comment='Нарушение правил группы КомуЧё Надым. Размещение ссылок в постах.Читайте правила и ответы на часто задаваемые вопросы. С Уважением КомуЧё Надым';
            $end_date = time()+259200;
        }
        elseif ($arrData['command']=='Ссылка')
        {
            $comment='Нарушение правил группы КомуЧё Надым. Размещение ссылок в постах.Читайте правила и ответы на часто задаваемые вопросы. С Уважением КомуЧё Надым';
            $end_date = time()+259200;
        }        
        $params=array('group_id'=> $this->group_id_kndm1, //положительное
            'owner_id'=> $Usersvk->user_id, //юзер +, группа -
            'end_date'=> $end_date,
            'comment'=> $comment,
            'comment_visible'=> 1,
        );                
        $groups_ban = VK::groups_ban($this->token_moderator,$params,array('name'=>'groups_ban','log_name' => $this->log_name));  
    }

}//end class