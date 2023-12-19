<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GenerateLabelsPersonalController extends Controller
{
    public function index()
    {
        return Inertia::render('NonPerekat/Personal/GenerateLabel');
    }
}
