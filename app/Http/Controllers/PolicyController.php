<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function terms()
    {
        return view('policies.terms');
    }

    public function privacy()
    {
        return view('policies.privacy');
    }

    public function cookies()
    {
        return view('policies.cookies');
    }

    public function guidelines()
    {
        return view('policies.guidelines');
    }

    public function acknowledgments()
    {
        return view('policies.acknowledgments');
    }

    public function licenses()
    {
        return view('policies.licenses');
    }

    public function moderation()
    {
        return view('policies.moderation');
    }
}