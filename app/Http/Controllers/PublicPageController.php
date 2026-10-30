<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('public.home');
    }

    public function how(): View
    {
        return view('public.how');
    }

    public function who(): View
    {
        return view('public.who');
    }

    public function faq(): View
    {
        return view('public.faq');
    }

    public function privacy(): View
    {
        return view('public.privacy');
    }

    public function terms(): View
    {
        return view('public.terms');
    }
}
