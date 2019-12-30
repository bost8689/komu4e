<?php

namespace Komu4e\Http\Controllers\Komuche_ndm;

use Illuminate\Http\Request;
use Komu4e\Http\Controllers\Controller;

use Komu4e\Model\Komuche_ndm\Order;
use Komu4e\Model\Komuche_ndm\Usersvk;
// use Komu4e\Model\Komuche_ndm\Postmessage;
// use Komu4e\Model\Komuche_ndm\Photospostmessage;
// use Komu4e\Model\Komuche_ndm\Settings;
// use Komu4e\User;

// use Komu4e\Http\Controllers\VK;
// use Auth;

class ProfitController extends Controller
{
    private $group_id_kndm = Null; //
    private $token_moderator = Null;
    public $mode_debug = 0; //режим отлади //if($this->mode_debug){}

    public function __construct()
    {
        $this->token_moderator=config('vk.token_moderator');
        $this->group_id_kndm=config('vk.group_id_kndm1');
    }

    public function profitGetThisMonth()
    {  
        $debug = array('Отладка'=>'ProfitController.profitThisMonth');
        $profit = array();
        
        $OrdersThisMonth = Order::orderBy('created_at', 'desc')->where('created_at','>',date('Y-m-01 00:00:00'))->get();        
        $profit['Посты от своего имени в этом месяце']='';
        $profit['Заказов в этом месяце']=$OrdersThisMonth->sum('ordered');
        $profit['Выполнено в этом месяце']=$OrdersThisMonth->sum('executed');
        $profit['Выручка в этом месяце']=$OrdersThisMonth->sum('ordered')*100;
        //Отладка
        if($this->mode_debug){    
            $debug['Прибыль в прошлом месяце $profit'] = $profit;  
            $debug['Заказы в этом месяце $OrdersThisMonth'] = $OrdersThisMonth;
            dump($debug);
        }  
        return $profit;
        
    }

        public function profitGetBackMonth()
    {  
        $debug = array('Отладка'=>'ProfitController.profitBackMonth');
        $profit = array(); 

        $OrdersBackMonth = Order::orderBy('created_at', 'desc')->where('created_at','>',date('Y-m-01 00:00:00',strtotime("-1 month")))->where('created_at','<',date('Y-m-01 00:00:00'))->get();
        $profit['Посты от своего имени в прошлом месяце']='';
        $profit['Заказов в прошлом месяце']=$OrdersBackMonth->sum('ordered');
        $profit['Выполнено в прошлом месяце']=$OrdersBackMonth->sum('executed');
        $profit['Выручка в прошлом месяце']=$OrdersBackMonth->sum('ordered')*100;
                      
        //Отладка
        if($this->mode_debug){
            $debug['Прибыль в прошлом месяце $profit'] = $profit;
            $debug['Заказы месяц назад $OrdersBackMonth'] = $OrdersBackMonth;
            dump($debug);
        }  
        return $profit;
        
    }

}
