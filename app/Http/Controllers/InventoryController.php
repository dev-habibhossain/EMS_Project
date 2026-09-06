<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersModuleIndex;
use Inertia\Response;

class InventoryController extends Controller
{
    use RendersModuleIndex;

    public function index(): Response
    {
        return $this->moduleIndex('Inventory');
    }

    public function damaged(): Response
    {
        return $this->moduleIndex('Damaged stock');
    }
}
