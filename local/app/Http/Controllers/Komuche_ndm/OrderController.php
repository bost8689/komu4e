<?php

namespace Komu4e\Http\Controllers\Komuche_ndm;

use Illuminate\Http\Request;
use Komu4e\Http\Controllers\Controller;

// use App\Http\Requests;

use Komu4e\Model\Komuche_ndm\Order;
use Komu4e\Model\Komuche_ndm\Usersvk;
// use Komu4e\Model\Komuche_ndm\Postmessage;
// use Komu4e\Model\Komuche_ndm\Photospostmessage;
use Komu4e\Model\Komuche_ndm\Settings;
use Komu4e\User;

use Komu4e\Http\Controllers\VK;
use Auth;

class OrderController extends Controller
{
    private $group_id_kndm = Null; //публикация логов //if($this->log_name){}
    private $token_moderator = Null;
    public $mode_debug = 0; //режим отлади //if($this->mode_debug){}

    public function __construct()
    {
        $SettingsModeDebug=Settings::where('name','komu4e_ndm_debug_mode')->first();
        if ($SettingsModeDebug->value2=="Включено") {
            $this->mode_debug = 1;
        }
        
        $this->token_moderator=config('vk.token_moderator');
        $this->group_id_kndm=config('vk.group_id_kndm1');
    }

    public function view(Request $request)
    {  
        //включить отладку или нет
        if (!empty($request->input('debug'))) {
            $debug = array('Отладка'=>'OrderController.view');
            $this->mode_debug=1;
        }
        
        $Orders = Order::with('Usersvk')->where('status',Null)->orderBy('id', 'desc')->get();

        //Отладка
        if($this->mode_debug){
            $debug['Заказы $Orders']=$Orders;
            dump($debug);
        }  

        //подключаю прибыль и получаю значения
        $ProfitController = new ProfitController();               
        $ProfitController->mode_debug = $this->mode_debug;
        $Profit=array(); 
        $Profit['profitThisMonth']=$ProfitController->profitGetThisMonth();
        $Profit['profitBackMonth']=$ProfitController->profitGetBackMonth();

        return view('komuche_ndm.order.view_order',['Orders'=>$Orders,'Profit'=>$Profit]);
        
    }
    //обработка выбора из списка какой заказ добавить
    public function add(Request $request)
    {         
        $collectOrder['order_type']=$request->input('order_type');
        //Отладка
        if($this->mode_debug){
            $debug = array('Отладка'=>'OrderController.add');
            $debug['данные $collectOrder']=$collectOrder;
            dump($debug);
        } 
        return view('komuche_ndm.order.add_order',['collectOrder'=>collect($collectOrder)]);
        //dump('add.order');
    }
    //обрабоатываю добавленный заказ
    public function processing_add(Request $request)
    {        
        $orderType = $request->input('order_type');
        $orderDate = $request->input('order_date');
        $orderCount = $request->input('order_count');
        $orderDate = $request->input('order_date');
        $usersvkId = $request->input('usersvk_id');
        $orderComment = $request->input('order_comment');

        if ($orderType=='' or $orderCount=='' or $usersvkId=='') {
            $result_processing_add='Не заполнены основные поля';            
            return view('komuche_ndm.order.processing_add_order',['result_processing_add'=> $result_processing_add]);
        } 

        $usersvkId=trim($usersvkId); //удаляю пробелы        
        $pos1 = strpos($usersvkId, 'https', 0); // $pos = 7, not 0
        $pos2 = strpos($usersvkId, 'vk.com/', 0); // $pos = 7, not 0     
        if ($pos1 === false or $pos2 === false) {            
            $result_processing_add='Не правильно вставлена ссылка';            
            return view('komuche_ndm.order.processing_add_order',['result_processing_add'=> $result_processing_add]);
        } 
        else {
            $userId=substr($usersvkId,15); //обрезаю с 15 символ

            $params = array('user_ids' => $userId,'fields' => 'photo_100');
            $usersGet = VK::usersGet($this->token_moderator,$params,Null);                            
            $firstName = $usersGet[0]['first_name'];
            $lastName = $usersGet[0]['last_name'];
            $photo = $usersGet[0]['photo_100'];
            $userId = $usersGet[0]['id'];            
            //поиск и добавление нового пользователя ВК
            $Usersvk = Usersvk::where('user_id',$userId)->first();
            if(!isset($Usersvk)){ 
                $Usersvk = Usersvk::create(['user_id'=>$userId,'firstname'=>$firstName,'lastname'=>$lastName,'photo'=>$photo]);
            }                           
            else{ 
                $Usersvk->photo = $photo;
                $Usersvk->save();
            } 
            //dump($Usersvk);
        }

        switch ($orderType) {
            case 'Пост от своего имени':
                $orderPrice = 100;
                break;
            case 'Пост от имени группы':
                $orderPrice = 350;
                break;
            case 'Закреп':
                $orderPrice = 1000;
                break;
            case 'Визитка':
                $orderPrice = 300;
                break;
            default:
                dd('Незивестный тип поста');               
                break;
        }

        $Order = Order::create(['usersvk_id'=>$Usersvk->id,'type'=>$orderType,'price'=>$orderPrice,'ordered'=>$orderCount,'executed'=>Null,'status'=>Null,'comments'=>$orderComment,'users_id'=>Auth::id()]);

        //Отладка
        if($this->mode_debug){
            $debug = array('Отладка'=>'OrderController.processing_add');
            $debug['Входящие данные $request->all()']=$request->all();
            dump($debug);
        }

        return redirect()->route('view_order');
        
    }

    public function delete_order(Request $request)
    {
        if($request->has('order_id')){
            foreach ($request->input('order_id') as $orderId) {
                $Order = Order::find($orderId);
                $Order->delete();
            }
        } 
        return redirect()->route('view_order');
    }

}
