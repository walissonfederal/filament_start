<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function privacyPolicy(Request $request)
    {
        return view('privacy-policy');
    }
}
