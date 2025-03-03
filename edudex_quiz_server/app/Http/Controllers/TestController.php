<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    public function admin()
    {
        return view('test.admin');
    }

    public function teacher()
    {
        return view('test.teacher');
    }

    public function staff()
    {
        return view('test.staff');
    }
} 