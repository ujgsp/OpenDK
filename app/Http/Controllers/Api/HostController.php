<?php

namespace App\Http\Controllers\Api;

use App\Models\Host;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\HostResource;

class HostController extends Controller
{
    public function index()
    {
        return HostResource::collection(Host::all());
    }
}
