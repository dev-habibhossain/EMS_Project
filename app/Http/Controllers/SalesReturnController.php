<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersModuleIndex;
use Inertia\Response;

class SalesReturnController extends Controller
{
    use RendersModuleIndex;

    public function index(): Response
    {
        return $this->moduleIndex('Sales returns');
    }
}
