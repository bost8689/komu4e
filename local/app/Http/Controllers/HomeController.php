<?php
//home1
namespace Komu4e\Http\Controllers;

use Illuminate\Http\Request;


class HomeController extends Controller
{


    public $group_id_kndm1 = 1; //публикация логов //if($this->log_name){}
    
    

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->group_id_kndm1=config('vk.group_id_kndm1');

    //     $this->middleware('permission');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //$this->group_id_kndm3 = 555;
        //dump($this->group_id_kndm1);
        
        return view('home');    
        //return view('home');
    }
}
