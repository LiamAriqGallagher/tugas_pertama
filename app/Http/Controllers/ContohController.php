<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContohController extends Controller
{
    public function index()
    {
        $title = 'contoh halaman blade.';
        $products = ['laptop', 'keyboard', 'mouse', 'monitor'];
        return view(    'contoh', compact('products','title'));
    }
}
