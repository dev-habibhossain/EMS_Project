<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersModuleIndex;
use Inertia\Response;

class PaymentController extends Controller
{
    use RendersModuleIndex;

    public function index(): Response
    {
        return $this->moduleIndex('Payments');
    }
}
