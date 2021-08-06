<?php

namespace App\Http\Controllers;

use App\Models\CrossSetting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $crossSetting = CrossSetting::where('id', 1)->first();
        $crossSettingOld = CrossSetting::where('id', 2)->first();
        return view('home', compact('crossSetting', 'crossSettingOld'));
    }
}
