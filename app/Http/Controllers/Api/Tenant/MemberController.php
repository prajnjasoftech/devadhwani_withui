<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Member;

class MemberController extends Controller
{
    public function index()
    {
        return Member::all();
    }
}
