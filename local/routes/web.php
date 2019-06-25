<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//use App\Http\Middleware\PermissionMiddleware;

use Intervention\Image\ImageManager;

Route::get('/', function () {
	//$manager = new ImageManager(array('driver' => 'gd'));
	
	//$img =Image::make('public/bar.jpg')->resize(950, 950)->insert('public/Лого КомуЧё без фона.png','center',0,0)->save('public/bar2.jpg');
	//$image = $manager->make('public/foo.jpg')->resize(300, 200);
	
	//return $img->response();
    
    return view('welcome');
});

Auth::routes();

Route::group(['middleware' => ['auth','permission']], function () {

	// Route::get('/home', 'HomeController@index')->middleware('auth')->name('home');
	Route::get('/home', 'HomeController@index')->name('home');

	Route::post('/UpdateEvent', 'Komuche_ndm\UpdateEventController@updateEvent')->name('updateEvent');

	//Postmessage
	Route::match(['get', 'post'],'/postmessage', 'Komuche_ndm\PostmessageController@view')->name('view_postmessage');
	Route::post('/postmessage/processing', 'Komuche_ndm\PostmessageController@processing')->name('processing_postmessage');
	Route::match(['get', 'post'],'/postmessage/find', 'Komuche_ndm\PostmessageController@find')->name('find_postmessage');

	//Bnip
	Route::match(['get', 'post'],'/bnip', 'Komuche_ndm\BnipController@view')->name('view_bnip');
	Route::post('/bnip/processing', 'Komuche_ndm\BnipController@processingBnip')->name('processingBnip');
	Route::post('/bnip/message', 'Komuche_ndm\BnipController@view_message')->name('view_message_bnip');
	Route::post('/bnip/message/processing', 'Komuche_ndm\BnipController@processing_message')->name('processing_message_bnip');
	

	//Order
	Route::match(['get', 'post'],'/order','Komuche_ndm\OrderController@view')->name('view_order');
	Route::post('/order/add','Komuche_ndm\OrderController@add')->name('add_order');
	Route::post('/order/processing','Komuche_ndm\OrderController@processing_add')->name('processing_add_order');
	Route::post('/order/delete','Komuche_ndm\OrderController@delete_order')->name('delete_order');
	//Route::get('/order/add/processing', function () {  abort(404); });


	//Message
	Route::post('/message','Komuche_ndm\MessageController@view')->name('view_message');
	Route::post('/message/processing','Komuche_ndm\MessageController@processingMessage')->name('processingMessage');



});

//callback
Route::post('/callback/auto','Komuche_ndm\CallbackController@index');





//Route::get('/home', 'HomeController@index')->name('home')->middleware('auth','permission');


//поиск постов
/*Route::post('/find/postmessage', 'Komuche_ndm\FindPostmessageController@processing')->name('processing_FindPostmessage')->middleware('auth');
Route::get('/find/postmessage', function () {  abort(404); });*/




//Bnip 
/*Route::get('/bnip','ki_ndm\BnipController@view')->name('view_bnip')->middleware('auth');
Route::post('/bnip','ki_ndm\BnipController@processing')->name('processing_bnip')->middleware('auth');
Route::get('/bnip/mess','ki_ndm\BnipController@view_mess')->name('view_bnip_mess')->middleware('auth');
Route::post('/bnip/mess','ki_ndm\BnipController@processing_mess')->name('processing_bnip_mess')->middleware('auth');*/



//Callback bnip
/*Route::post('/ki_ndm/callback/bnip','ki_ndm\callback\BnipCallback@Callback');
Route::get('/ki_ndm/callback/bnip', function () {  abort(404); });*/



