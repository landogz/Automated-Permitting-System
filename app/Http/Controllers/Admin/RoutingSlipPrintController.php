<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoutingSlip;
use App\Support\Evaluation\EvaluationFormCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoutingSlipPrintController extends Controller
{
    /**
     * Government-letterhead print for QMS-61 routing slip / QMS-62 path summary.
     */
    public function __invoke(Request $request, RoutingSlip $slip): View
    {
        $doc = strtolower((string) $request->query('doc', EvaluationFormCatalog::DOC_QMS_61));
        if (! in_array($doc, [EvaluationFormCatalog::DOC_QMS_61, EvaluationFormCatalog::DOC_QMS_62], true)) {
            throw new NotFoundHttpException('Unknown routing document.');
        }

        $slip->load(['application', 'template', 'generatedBy', 'steps.department']);

        return view('admin.routing-slip-print', [
            'slip' => $slip,
            'doc' => $doc,
            'formCode' => $doc === EvaluationFormCatalog::DOC_QMS_62 ? 'QMS-62' : 'QMS-61',
            'formTitle' => $doc === EvaluationFormCatalog::DOC_QMS_62
                ? 'Routing Path Template Instance'
                : 'Official Routing Slip',
        ]);
    }
}
