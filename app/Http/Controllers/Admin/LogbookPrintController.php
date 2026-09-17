<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogbookEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LogbookPrintController extends Controller
{
    /**
     * Printable logbook entry view (browser print / PDF via print dialog).
     */
    public function __invoke(Request $request, LogbookEntry $logbook): View
    {
        $logbook->load(['application', 'recordedByUser']);

        return view('admin.logbook-print', [
            'entry' => $logbook,
        ]);
    }
}
