<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Event;

class EventController extends Controller
{
    public function index()
    {
        return Event::all();
    }
}
