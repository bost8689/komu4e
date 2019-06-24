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
    public $mode_update = 0; //обновление перед показом включено //if($this->mode_update){}
    public $log_write = 1; //публикация логов //if($this->log_write){}
    public $mode_debug = 0; //режим отлади //if($this->mode_debug){}
    public $log_name = 'komuche_ndm_postmessage'; //публикация логов //if($this->log_name){}
    private $group_id_kndm1 = Null; //публикация логов //if($this->log_name){}
    private $token_moderator = Null;
    public $mode_banUsersvk = 1;
    
        public function __construct()
    {
        $this->token_moderator=config('vk.token_moderator');
        $this->group_id_kndm1=config('vk.group_id_kndm1');
    }
   
        

	//отображение постов
    public function view(Request $request){       
        if($this->mode_debug){dump('*** PostmessageController.view_postmessage'); dump('$request->all()',$request->all());}      

        $date_view_wall = $request->input('date_view_wall');
        $checkbox_all = $request->input('checkbox_all');
        $checkbox_status_is = $request->input('checkbox_status_is');

        //если это не показать выполненные то показывается обновление
        if(!isset($checkbox_status_is)){
            //обновление
            $UpdateEventController = new UpdateEventController();
            //$test=1;
            if($this->mode_update) {$resultUpdateEvent = $UpdateEventController->updateEvent($request);}
        }
        


        // $Postmessages = Postmessage::with('usersvk')->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();//коллекция объектов 

        //чтобы получить уникальных юзеров
        if (isset($checkbox_all)) {$Postmessages=Postmessage::with('usersvk')->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();}
        elseif(isset($checkbox_status_is)){$Postmessages=Postmessage::with('usersvk')->whereNotNull('status')->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();}
        else{$Postmessages=Postmessage::with('usersvk')->whereNull('status')->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();}

        //тренировка с коллекциями
        //$collection = collect(['333' =>['product_id' => 1111, 'name' => 'Des1'],'2222' =>['product_id' => 222, 'name' => 'Des2']]);
        //$collection->put('111',['product_id' => 333, 'name' => 'Desk3']);
        //$usersvk->push($Postmessage->usersvk->user_id); /.в конец

        $collectUsersvk = collect([]);
        foreach ($Postmessages as $k_Postmessage => $Postmessage) {
            $collectUsersvk->push(['id' => $Postmessage->usersvk->id,'user_id' => $Postmessage->usersvk->user_id]);            
        }
        $collectUsersvk = $collectUsersvk->unique('user_id');
        if($this->mode_debug){dump('unique',$collectUsersvk);}        

        $collectPostmessages = collect([]);
        foreach ($collectUsersvk as $k_usersvk => $v_usersvk) {
            if (isset($checkbox_all)) {$Postmessages=Postmessage::with('photospostmessage','user')->where('usersvk_id',$v_usersvk['id'])->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();}
            elseif (isset($checkbox_status_is)) {$Postmessages=Postmessage::with('photospostmessage','user')->where('usersvk_id',$v_usersvk['id'])->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();}
            else{$Postmessages=Postmessage::with('photospostmessage','user')->where('usersvk_id',$v_usersvk['id'])->where('status',Null)->whereBetween('date', [$date_view_wall.' 00:00:00',$date_view_wall.' 23:59:59'])->orderBy('date', 'desc')->get();}
            $collectPostmessages->push(['Usersvk' => Usersvk::where('id','=',$v_usersvk['id'])->first(),'Orders' => Order::where('usersvk_id','=',$v_usersvk['id'])->where('status','=',Null)->get(),'Postmessages' => $Postmessages]);
        }
        if($this->mode_debug){dump('коллекция $collectPostmessages',$collectPostmessages);}
        //dump($resultUpdateEvent);
        return view('komuche_ndm.postmessage.view_postmessage',['collectPostmessages' => $collectPostmessages]); 
    } //end function view

    //обновление ыы
    public function updatePostmessage(array $arr_data){ //входят обновления updates из UpdateEventController

            $token_moderator=config('vk.token_moderator');
        	$group_id_kndm1=config('vk.group_id_kndm1');  
        	$countAddDBPostmessage = 0;
        	$countAddDBUsersvk = 0;
            //логирование
    		$v_updates = $arr_data['v_updates'];
            if($this->mode_debug){dump('№события=',$arr_data['k_updates']);}
            if($this->mode_debug){dump('v_updates',$arr_data['v_updates']);}

            if ($v_updates['type']=='wall_post_new') { //новый пост
                //проверяем есть ли такой пост у меня в базе                
                //если нет заносим в базу данных
                if($this->log_write){Log::channel($this->log_name)->info('array',$v_updates);}                
                $Postmessage = Postmessage::where('source_id','=',$v_updates['object']['id'])->first();
                if (empty($Postmessage)) {
                		$userId = $v_updates['object']['from_id'];
                		if ($v_updates['object']['from_id']>0) {
                			$params = array('user_ids' => $userId,'fields' => 'photo_100');
                		    $usersGet = VK::usersGet($token_moderator,$params,Null);                		    
                		    $firstName = $usersGet[0]['first_name'];
                		    $lastName = $usersGet[0]['last_name'];
                		    $photo = $usersGet[0]['photo_100'];
                		} 
                		else{
                			$params = array('group_id' => abs($v_updates['object']['from_id']),'fields' => '');
                			$groupsGetById = VK::groupsGetById($token_moderator,$params,Null);   
                			dump($groupsGetById);       
                   			$firstName = $groupsGetById[0]['name'];              
                   			$lastName = Null;
                   			$photo = $groupsGetById[0]['photo_100'];
                		}
                        //поиск и добавление нового пользователя ВК
                        $Usersvk = Usersvk::where('user_id',$userId)->first();
                        if(!isset($Usersvk)){ 
                        	$Usersvk = Usersvk::create(['user_id'=>$userId,'firstname'=>$firstName,'lastname'=>$lastName,'photo'=>$photo]);
                        	$countAddDBUsersvk++;
                        }                        	
                        else{ 
                            $Usersvk->photo = $photo;
                            $Usersvk->save();
                        }                    
                    
                    //создаю новый объект
                    $Postmessage = Postmessage::create(['source_id'=>$v_updates['object']['id'],'usersvk_id'=>$Usersvk->id,'text'=>$v_updates['object']['text'],'date'=>date('Y-m-d H:m:s',$v_updates['object']['date']),'user_id'=>1,'status'=>Null,'type_status'=>Null]);
                    $countAddDBPostmessage++;
                    //if($this->mode_debug){dump('Запись в БД $Postmessage',$Postmessage);} 
                    //перебираю фото этого поста
                    if (isset($v_updates['object']['attachments'])){                        
                        foreach ($v_updates['object']['attachments'] as $k_attachment => $v_attachment) {
                            if($v_attachment['type']=='photo'){
                                //if($this->mode_debug){dump('metka type photo');}                                 
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
                                        $$url_photo_type_r = $v_sizes['url'];//"width":320,"height":240
                                    }
                                    else{
                                        Log::channel($this->log_name)->error('неизвестный тип для фото $v_updates',$v_updates);
                                        dump('неизвестный type photo $v_updates',$v_updates);
                                    }
                                }
                                //проверяю на наличие url ссылки                               
                                if($this->log_write){Log::channel($this->log_name)->info('array v_updates',$v_updates);}

                                if(isset($url_photo_type_m)){$url_photo_type_min=$url_photo_type_m;}
                                    elseif (isset($url_photo_type_x)) {$url_photo_type_min=$url_photo_type_x;}
                                    elseif (isset($url_photo_type_p)) {$url_photo_type_min=$url_photo_type_p;}
                                    elseif (isset($url_photo_type_q)) {$url_photo_type_min=$url_photo_type_q;}
                                    elseif (isset($url_photo_type_r)) {$url_photo_type_min=$url_photo_type_r;}
                                    elseif (isset($url_photo_type_y)) {$url_photo_type_min=$url_photo_type_y;}
                                    else{dump('не найдена url для url_photo_type_min',$v_attachment);
                                    Log::channel($this->log_name)->error('не найдена url для url_photo_type_min',$v_attachment);
                                }

                                if(isset($url_photo_type_y)){
                                    $url_photo_type_max=$url_photo_type_y;}
                                elseif (isset($url_photo_type_z)) {$url_photo_type_max=$url_photo_type_z;}
                                elseif (isset($url_photo_type_w)) {$url_photo_type_max=$url_photo_type_w;}
                                elseif (isset($url_photo_type_o)) {$url_photo_type_max=$url_photo_type_o;}                                
                                else{dump('не найдена url для url_photo_type_max',$v_attachment);
                                Log::channel($this->log_name)->error('не найдена url для url_photo_type_max',$v_attachment);
                                }
                                $Photospostmessage = Photospostmessage::create(['filenamemax'=>Null,'pathmax'=>Null,'typemax'=>Null,'photomax_url'=>$url_photo_type_max,'filenamemin'=>Null,'pathmin'=>Null,'typemin'=>Null,'photomin_url'=>$url_photo_type_min,'postmessage_id'=>$Postmessage->id]);
                            }                            
                        }  
                    }
                    //dump($Postmessage->id);
                }
            }
            else{
                dump('Неизвестный тип в PostmessageController $v_updates[type]',$v_updates['type']);
                Log::channel($this->log_name)->error('Неизвестный тип поста $v_updates[type]',[$v_updates['type']]);
                Log::channel($this->log_name)->error('array $v_updates',[$v_updates]);                      
            }
            $result['countAddDBPostmessage'] = $countAddDBPostmessage;
            $result['$countAddDBUsersvk'] = $countAddDBUsersvk;

            return $result;
    } //end function update

    //обработка полученных данных
    public function processing(Request $request){
        $token_moderator=config('vk.token_moderator');
        $group_id_kndm1=config('vk.group_id_kndm1');
        if($this->mode_debug){dump('PostmessageController.processing_postmessage');dump('$request->all()',$request->all());}
        if($this->mode_debug){dump('$request->input(processing)',$request->input('processing'));}

        foreach ($request->input('processing') as $k_processing => $v_processing) {
            $Usersvk = Usersvk::find($v_processing['usersvk_id']);

            foreach ($v_processing['postmessage_id'] as $postmessage_id => $command) {
                $Postmessage = Postmessage::with('photospostmessage')->find($postmessage_id); //Нахожу по id этот пост у себя в БД
                if ($command=='Удалить') {   
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command)); 
                    $Postmessage->status=$command;
                }
                elseif($command=='Реклама'){  
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);                  
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command));
                    if($this->mode_banUsersvk){ $this->banUsersvk(array('command'=>$command),$Usersvk); }
                    $Postmessage->status=$command;                                     
                }
                elseif($command=='Повтор'){
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command));
                    if($this->mode_banUsersvk){ $this->banUsersvk(array('command'=>$command),$Usersvk); }
                    $Postmessage->status=$command;
                }
                elseif($command=='Ссылка'){
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command));
                    if($this->mode_banUsersvk){ $this->banUsersvk(array('command'=>$command),$Usersvk); } 
                    $Postmessage->status=$command;
                    //сохраняю фотографии 
                }
                elseif($command=='Более3'){
                    $this->savePhoto(array('type'=>'postmessage'),$Postmessage);                    
                    $this->deletePostmessage(array('post_id'=>$Postmessage->source_id,'status'=>$command));
                    if($this->mode_banUsersvk){ $this->banUsersvk(array('command'=>$command),$Usersvk); }
                    $Postmessage->status=$command;                    
                }
                elseif($command=='Найдено'){                    
                    $Postmessage->status=$command;
                    $Bnip = Bnip::create(['source_id'=>Null,'type_source'=>'group','post_id'=>Null,'type_post'=>Null,'usersvk_id'=>$Usersvk->id,'text'=>$Postmessage->text,'user_id'=>Auth::user()->id,'status'=>$command,'type_status'=>Null]);
                    $this->savePhoto(array('type'=>$command,'Bnip'=>$Bnip),$Postmessage);
                }
                elseif($command=='Потеряно'){                    
                    $Postmessage->status=$command;
                    $Bnip = Bnip::create(['source_id'=>Null,'type_source'=>'group','post_id'=>Null,'type_post'=>Null,'usersvk_id'=>$Usersvk->id,'text'=>$Postmessage->text,'user_id'=>Auth::user()->id,'status'=>$command,'type_status'=>Null]);
                    $this->savePhoto(array('type'=>$command,'Bnip'=>$Bnip),$Postmessage);
                }
                elseif($command=='Заказ'){                    
                    if($this->mode_debug){dump('Тут команда Заказ',$postmessage_id,$command);dump('$v_processing[Orders]',$v_processing['Orders']);}
                    //кол-во заказов
                    $count_orders = count($v_processing['Orders']);
                    foreach ($v_processing['Orders'] as $k_order => $v_order) {                        
                        if($k_order==$count_orders-1) { //если это последний заказ
                            $Order = Order::where('id',$v_order['id'])->first();
                            if($this->mode_debug){dump('заказ до',$Order);}
                            $Order->executed=$Order->executed+1;
                            if($Order->executed>=$Order->ordered){
                                $Order->status='Выполнен';                                
                            }                            
                            $Order->save();                            
                            if($this->mode_debug){dump('заказ после',$Order);}
                        }                      
                    }                    
                    $Postmessage->status=$command;
                    $Postmessage->type_status=$Order->id;
                    $this->savePhoto(array('type'=>$command),$Postmessage);
                }
                else {
                    //стоит галочка просмотрено
                    if (!empty($request->input('checkbox_prosmotreno'))) {
                        if ($Postmessage->status==Null) {
                            $Postmessage->status='Просмотрено';
                        }                            
                    }
                }
                $Postmessage->user_id=Auth::user()->id; 
                $Postmessage->save();
            }
        }
        return view('komuche_ndm.postmessage.processing_postmessage');
    }

    public function savePhoto(array $arr_data,$Postmessage){

        foreach ($Postmessage->photospostmessage as $k_photo => $Photo) {
            $typeMax = 'jpg';
            $fileNameMax = $Postmessage->id.'_'.$k_photo.'.'.$typeMax;
            if ($arr_data['type']=='postmessage') {
                $pathMax = 'public/komu4e_ndm/postmessage/';
                $img =Image::make($Photo->photomax_url)->save($pathMax.$fileNameMax);
                                
            }
            elseif($arr_data['type']=='Найдено'){
                $pathMax = 'public/komu4e_ndm/bnip/naideno/';
                $Bnip = $arr_data['Bnip'];
                Photosbnip::create(['filenamemax'=>$fileNameMax,'pathmax'=>$pathMax,'bnip_id'=>$Bnip->id]);
                $img =Image::make($Photo->photomax_url);
                $img->insert('public/bnipWatermark.png', 'bottom-right')->save($pathMax.$fileNameMax);
            }
            elseif($arr_data['type']=='Потеряно'){
                $pathMax = 'public/komu4e_ndm/bnip/poteryano/';
                $Bnip = $arr_data['Bnip'];
                Photosbnip::create(['filenamemax'=>$fileNameMax,'pathmax'=>$pathMax,'bnip_id'=>$Bnip->id]);

                $img =Image::make($Photo->photomax_url);
                $img->insert('public/bnipWatermark.png', 'bottom-right')->save($pathMax.$fileNameMax);
            }
            elseif($arr_data['type']=='Заказ'){
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
        return;   
    }

    //Удаление поста и запись 
    public function deletePostmessage(array $arr_data){        
        $params = array ('owner_id' => -1 * $this->group_id_kndm1,'post_id' =>  $arr_data['post_id'] );
        $wall_delete = VK::wall_delete($this->token_moderator,$params,array('name'=>'wall_delete','log_name' => $this->log_name));
        // $Postmessage->status=$arr_data['status'];                    
        // $Postmessage->save(); 
        return $wall_delete;   
    }

    //блокировка пользователя за рекламу
    public function banUsersvk(array $arr_data,$Usersvk){

        if ($arr_data['command']=='Реклама') {
            $comment='Нарушение правил группы КомуЧё Надым. Коммерческое сообщение на стене без согласования администратором. На стене реклама платная. Читайте правила и ответы на часто задаваемые вопросы. С Уважением КомуЧё Надым';
            $end_date = time()+1209600;
        }
        elseif ($arr_data['command']=='Повтор')
        {
            $comment='Нарушение правил группы КомуЧё Надым. Повторные (по смыслу, тексту, фото) сообщения за период в течении суток .Читайте правила и ответы на часто задаваемые вопросы. С Уважением КомуЧё Надым';
            $end_date = time()+604800; //604800 1 неделя
        }
        elseif ($arr_data['command']=='Более3')
        {
            $comment='Нарушение правил группы КомуЧё Надым. Размещение ссылок в постах.Читайте правила и ответы на часто задаваемые вопросы. С Уважением КомуЧё Надым';
            $end_date = time()+259200;
        }
        elseif ($arr_data['command']=='Ссылка')
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