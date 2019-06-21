<?php

namespace Komu4e\Http\Controllers\Komuche_ndm;

use Illuminate\Http\Request;
use Komu4e\Http\Controllers\Controller;

// use App\Http\Requests;

use Komu4e\Model\Komuche_ndm\Order;
use Komu4e\Model\Komuche_ndm\Usersvk;
use Komu4e\Model\Komuche_ndm\Postmessage;
use Komu4e\Model\Komuche_ndm\Photospostmessage;
use Komu4e\Model\Komuche_ndm\Settings;
use Komu4e\User;
use Auth;

class OrderController extends Controller
{

    public function view()
    {  
        
        
    }
    //обработка выбора из списка какой заказ добавить
    public function add(Request $request)
    { 
        
        $collectOrder['order_type']=$request->input('order_type');
        return view('komuche_ndm.order.add_order',['collectOrder'=>collect($collectOrder)]);
        //dump('add.order');
    }
    //обрабоатываю добавленный заказ
    public function processing_add(Request $request)
    {
        dd($request->all());
    }

}
