<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamPeriod;

class DashboardController extends Controller
{
    public function index()
    {
        $examPeriod = ExamPeriod::latest()->first(); // hoặc logic lấy kỳ thi phù hợp
        return view('dashboard.index', compact('examPeriod'));
    }
} 