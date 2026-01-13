<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        return view('templates.index');
    }

    public function create()
    {
        return view('templates.create');
    }
}