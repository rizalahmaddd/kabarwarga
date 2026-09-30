<?php

namespace App\Http\Controllers;

use App\Support\HomeFeed;

class HomeController extends Controller
{
    public function __invoke(HomeFeed $feed)
    {
        return view('public.home', $feed->build());
    }
}
