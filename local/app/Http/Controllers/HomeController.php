<?php
//home1
namespace Komu4e\Http\Controllers;

use Illuminate\Http\Request;
//use Komu4e\Http\Controllers\MessageController;

use Viber\Bot;
use Viber\Api\Sender;
use Viber\Client;

/**
 * Build bot with viber client
 *
 * @author Stanislav
 */

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

    $apiKey = '4b8b89fab867d3d0-487a5c4f0a9c5af4-bff9cf5444355d1a';
    // reply name
    // $botSender = new Sender([
    //     'name' => 'komu4egrill',
    //     'avatar' => 'https://developers.viber.com/img/favicon.ico',
    // ]);    

    

    //$config = require('./config.php');

    //$apiKey = $config['apiKey']; // from PA "Edit Details" page
    $webhookUrl = "https://komu4e.ru/viber/bot/komu4egrill"; // for exmaple https://my.com/bot.php

    // try {
    //     $client = new Client(['token' => $apiKey]);
    //     $result = $client->setWebhook($webhookUrl);
    //     echo "Success!\n"; // print_r($result);
    // } catch (Exception $e) {
    //     echo 'Error: ' . $e->getMessage() . "\n";
    // }



        return view('home',['fileError' => $this->checkFileError()]);    
    }

    //проверяем наличие файла с ошибками
    public function checkFileError(){        
        $path = storage_path('logs/laravel*.log'); 
        $fileError=[];  
        foreach(glob($path) as $file) { 
        // далее получаем последний добавленный/измененный файл      
        $fileError[] = $file; // массив всех файлов       
        }
        return $fileError;           
    }
}




