<?php

namespace Komu4e\Http\Controllers\Komuche_ndm;

use Illuminate\Http\Request;
use Komu4e\Http\Controllers\Controller;

// use Komu4e\Model\Komuche_ndm\Order;
// use Komu4e\Model\Komuche_ndm\Usersvk;
// use Komu4e\Model\Komuche_ndm\Postmessage;
// use Komu4e\Model\Komuche_ndm\Photospostmessage;
use Komu4e\Model\Komuche_ndm\Settings;
use Komu4e\User;

use Komu4e\Http\Controllers\VK;
use Auth;

class SettingsController extends Controller
{
    public $mode_debug = 0;
    
    public function __construct()
    {
        $SettingsModeDebug=Settings::where('name','komu4e_ndm_debug_mode')->first();
        if ($SettingsModeDebug->value2=="Включено") {
            $this->mode_debug = 1;
        }
    }

    public function view(Request $request)
    {  
        $SettingsModeDebug=Settings::where('name','komu4e_ndm_debug_mode')->first();
        return view('komuche_ndm.settings.view',['SettingsModeDebug'=>$SettingsModeDebug]);        
    }

    public function processing(Request $request)
    {  
        
        $SettingsModeDebug=Settings::where('name','komu4e_ndm_debug_mode')->first();
        $SettingsModeDebug->value2 = $request->input('mode_debug');
        $SettingsModeDebug->save();
        return redirect()->route('view_settings');          
    }  



}
