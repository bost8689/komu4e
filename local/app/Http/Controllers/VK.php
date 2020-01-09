<?php

namespace Komu4e\Http\Controllers;

use Illuminate\Http\Request;

use VK\Client\VKApiClient;
use VK\Client\VKApiRequest;
use VK\Exceptions\VKApiException;

use Log;

class VK extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        //dump('test');
    }

    	//Получение данных пользователя
        static function usersGet($access_token,array $params,$data)
    {        
    	usleep(300000);
	    $vk = new VKApiClient();
	    try {	   
	    $response = $vk->users()->get($access_token, $params);	    
	    /*array(             
	        'user_ids' => $user_ids, //положительное 
	        'fields' => $fields,
	    )*/
	    return $response;
	    } catch (VKApiException $e) {
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }
		}	    
	    // Доступные значения: photo_id, verified, sex, bdate, city, country, home_town, has_photo, photo_50, photo_100, photo_200_orig, photo_200, photo_400_orig, photo_max, photo_max_orig, online, domain, has_mobile, contacts, site, education, universities, schools, status, last_seen, followers_count, common_count, occupation, nickname, relatives, relation, personal, connections, exports, wall_comments, activities, interests, music, movies, tv, books, games, about, quotes, can_post, can_see_all_posts, can_see_audio, can_write_private_message, can_send_friend_request, is_favorite, is_hidden_from_feed, timezone, screen_name, maiden_name, crop_photo, is_friend, friend_status, career, military, blacklisted, blacklisted_by_me. 
    } 

    //получение данных группы
    static function groupsGetById($access_token,$params,$data)
    { 
    	usleep(300000);       
	    $vk = new VKApiClient();	
	    try {	   
	    $response = $vk->groups()->getById($access_token,$params);
	    return $response; 
	    } catch (VKApiException $e) {
			dump('Сделайте пожалуйста скрин ошибки и сообщите администратору');
			dump($e->getMessage(),$params,$data);
	    	// Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$data]);
		}
	    // список дополнительных полей, которые необходимо вернуть. Например: city, country, place, description, wiki_page, market, members_count, counters, start_date, finish_date, can_post, can_see_all_posts, activity, status, contacts, links, fixed_post, verified, site, ban_info, cover.	    
    }
	/*array(             
	'group_id' => $group_id,
	'fields' => $fields,
	//'group_ids' => $group_ids,	        
	)*/

    //удаление поста у группы
    static function wall_delete($access_token,array $params,array $data)
    {  
    	usleep(300000);//  1000000 = 1 сек  
    	$vk = new VKApiClient();  
    	try { 
			$response = $vk->wall()->delete($access_token, $params);
		return $response;
		} catch (VKApiException $e) {
			dump('Сделайте пожалуйста скрин ошибки и сообщите администратору');
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }		
		} 
	    
    } 
    //блокировка пользователя в группе
    static function groups_ban($access_token,array $params,$data)
    {  
    	usleep(300000);      
	    $vk = new VKApiClient();
	    try {	   
	    $response = $vk->groups()->ban($access_token, $params);
	    return $response; 
	    } catch (VKApiException $e) {
			dump('Сделайте пожалуйста скрин ошибки и сообщите администратору');
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }	
		}
    }
    /*array(             
	    'group_id'=> $group_id, //положительное
	    'owner_id'=> $owner_id, //юзер +, группа -
	    'end_date'=> $end_date,
	    'comment'=> $comment,
	    'comment_visible'=> $comment_visible,
	 )*/

	//получение url для загрузки фотографий
	static function photosGetWallUploadServer($access_token,$params,$data)
    {    
    	usleep(300000);    
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->photos()->getWallUploadServer($access_token,$params);
			return $response;
		} catch (VKApiException $e) {
			dump('Сделайте пожалуйста скрин ошибки и сообщите администратору');
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }				
		}	    
    } 
    /*array(             
    'group_id' => $group_id,//идентификатор сообщества, на стену которого нужно загрузить фото (без знака «минус»).
	)*/

	static function requestUpload($params,$data)//type_file= photo
    {
    	usleep(300000);
    	$uploadUrl = $params['uploadUrl'];
    	$typeFile = $params['typeFile'];
    	$fileName = $params['fileName'];

	    $vk = new VKApiClient();	    
	    try { 
			$response = $vk->getRequest()->upload($uploadUrl, $typeFile, $fileName);
			return $response;
		} catch (VKApiException $e) {
			dump('Сделайте пожалуйста скрин ошибки и сообщите администратору');
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }
		}    
    }
    /*$params = array('uploadUrl' => $uploadUrl,'typeFile'=>'photo','fileName'=>$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$Photobnip->pathmax.$Photobnip->filenamemax);*/

    //сохраняю загрузку фото
    static function photosSaveWallPhoto($access_token, $params,$data)
    {   
    	usleep(300000); 	        
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->photos()->saveWallPhoto($access_token, $params);
	    	return $response;
		} catch (VKApiException $e) {
			dump('Сделайте пожалуйста скрин ошибки и сообщите администратору');
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }
		}  

		/*response array:1 [▼
		  0 => array:12 [▼
		    "id" => 456245237
		    "album_id" => -14
		    "owner_id" => 206862242
		    "photo_75" => "https://pp.userapi.com/c844722/v844722784/24fdb/ahIIeLQShUs.jpg"
		    "photo_130" => "https://pp.userapi.com/c844722/v844722784/24fdc/ALX3b_7QXKw.jpg"
		    "photo_604" => "https://pp.userapi.com/c844722/v844722784/24fdd/ffvpbz7iofU.jpg"
		    "photo_807" => "https://pp.userapi.com/c844722/v844722784/24fde/6HY5u0wMrcA.jpg"
		    "width" => 605
		    "height" => 807
		    "text" => ""
		    "date" => 1523436159
		    "access_key" => "5d47ae122df4737d55"
		  ]
		]*/
    }
    /*array(             
	'group_id' => $group_id,//идентификатор сообщества, на стену которого нужно загрузить фото (без знака «минус»).
	'photo' => $photo,
	'server' => $server,
	'hash' => $hash,
	)*/

	static function wallPost($access_token, $params,$data)
    {     
    	usleep(300000);	    
    	$vk = new VKApiClient();  
    	try { 
			$response = $vk->wall()->post($access_token, $params);
			return $response;
		} catch (VKApiException $e) {
			dump('Сделайте пожалуйста скрин ошибки и сообщите администратору');
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }
		} 
	    
    }
    /*array(             
		'owner_id' => $owner_id, //у группы отрицательное поле
		'friends_only' => $friends_only, //1 — запись будет доступна только друзьям, 0 — всем пользователям. По умолчанию публикуемые записи доступны всем пользователям. флаг, может принимать значения 1 или 0
		'from_group' => $from_group,//1 — запись будет опубликована от имени группы, 0 — запись будет опубликована от имени пользователя (по умолчанию). 
		'message' => $message,
		'attachments' => $attachments,
		'signed' => $signed, //1 — у записи, размещенной от имени сообщества, будет добавлена подпись (имя пользователя, разместившего запись), 0 — подписи добавлено не будет. Параметр учитывается только при публикации на стене сообщества и указании параметра 
		'publish_date' => $publish_date,
	)*/

	//получить историю диалога
	static function messagesGetHistory($access_token,$params,$data)
    {    	        
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->messages()->getHistory($access_token,$params);
	    	return $response;
		}catch (VKApiException $e) {
			dump('Сделайте пожалуйста скрин ошибки и сообщите администратору');
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }
		}
	} 		
	   
	//    array:5 [▼
	//   "count" => 14
	//   "unread" => 1
	//   "items" => array:5 [▶]
	//   "in_read" => 2652
	//   "out_read" => 2709
	// ]

	/*array(             
	'offset' => $offset,
	'count' => $count,
	'user_id' => $user_id,// 
	'rev' => $rev,//1 – возвращать сообщения в хронологическом порядке. 0 – возвращать сообщения в обратном хронологическом порядке (по умолчанию). 
	)*/

	//получить диалоги
	static function messagesGetConversations($access_token,$params,$data)
    {
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->messages()->getConversations($access_token,$params);
	    	return $response;
		}catch (VKApiException $e) {
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }
		}
	}  


	//Проверить не заблокирован ли пользователь
	static function groupsGetBanned($access_token,$params,$data)
    {
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->groups()->getBanned($access_token,$params);
	    	return $response;
		}catch (VKApiException $e) {
			if(!$e->getMessage() == 'Not found: user or group not banned in this group'){
				dump($e->getMessage(),$params,$data);
				// if(!empty($data['log_name'])){
				// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
				// }
			}
			
		}
	}

	/*array(             
	'group_id' => $offset,
	'offset' => $count,
	'count' => $user_id,// 
	'fields' => $rev,//1 – возвращать сообщения в хронологическом порядке. 0 – возвращать сообщения в обратном хронологическом порядке (по умолчанию).
	'owner_id' => $count, 	
	)*/

	//отправка сообщений
	static function messagesSend($access_token,$params,$data)
    {        
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->messages()->send($access_token, $params);
	    	return $response;
		} catch (VKApiException $e) {
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }			
		} 		
	    

    } 
    /*array(             
    'user_id' => $user_id,
    'message' => $message, //220409092 Вячеслав Тихонов
    //'group_ids' => $group_ids,	
	)*/

	//Одобряем заявку пользователя в группу
	static function groupsApproveRequest($access_token,$params,$data)
    {        
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->groups()->approveRequest($access_token, $params);
	    	return $response;
		} catch (VKApiException $e) {
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }			
		}
    } 

    //Проверяем состоит ли пользователь в группе
	static function groupsisMember($access_token,$params,$data)
    {        
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->groups()->isMember($access_token, $params);
	    	return $response;
		} catch (VKApiException $e) {
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }			
		}
    } 
    //получить заявки в группе
    	static function groupsgetRequests($access_token,$params,$data)
    {        
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->groups()->getRequests($access_token, $params);
	    	return $response;
		} catch (VKApiException $e) {
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }			
		}
    } 

    //Разблокировать пользователя
    	static function groupsUnban($access_token,$params,$data)
    {        
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->groups()->unban($access_token, $params);
	    	return $response;
		} catch (VKApiException $e) {
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }			
		}
    } 

    //Разблокировать пользователя
    	static function messagesMarkAsAnsweredConversation($access_token,$params,$data)
    {      
	    $vk = new VKApiClient();
	    try { 
			$response = $vk->messages()->markAsAnsweredConversation($access_token, $params);
	    	return $response;
		} catch (VKApiException $e) {
			dump($e->getMessage(),$params,$data);
			// if(!empty($data['log_name'])){
			// 	Log::channel($data['log_name'])->error('VK',[$e->getMessage(),$params,$data]);
			// }			
		}
    } 


}//class


