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
        //проверяем наличие файла с ошибками
        $path = storage_path('logs\laravel*.log'); 
        $fileError=[];  
        foreach(glob($path) as $file) { 
        // далее получаем последний добавленный/измененный файл      
        $fileError[] = $file; // массив всех файлов       
        }   
        return view('home',['fileError' => $fileError]);    
        //return view('home');
    }
}
