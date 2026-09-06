<?php

namespace App\Http\Controllers\Concerns;

use Inertia\Inertia;
use Inertia\Response;

trait RendersModuleIndex
{
    /**
     * Render the shared Kiln module shell. Swap this for a dedicated page when the module gets live data.
     */
    protected function moduleIndex(string $title): Response
    {
        return Inertia::render('ModuleIndex', [
            'title' => $title,
        ]);
    }
}
