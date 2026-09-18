<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Support\Evaluation\EvaluationFormCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EvaluationPrintController extends Controller
{
    /**
     * Government-letterhead print for QMS-63 evaluation sheet / QMS-64 technical findings.
     */
    public function __invoke(Request $request, Evaluation $evaluation): View
    {
        $doc = strtolower((string) $request->query('doc', EvaluationFormCatalog::DOC_QMS_63));
        if (! in_array($doc, [EvaluationFormCatalog::DOC_QMS_63, EvaluationFormCatalog::DOC_QMS_64], true)) {
            throw new NotFoundHttpException('Unknown evaluation document.');
        }

        $evaluation->load(['application', 'evaluator', 'step.department']);
        $findings = EvaluationFormCatalog::normalizeFindings($evaluation->findings);

        return view('admin.evaluation-print', [
            'evaluation' => $evaluation,
            'doc' => $doc,
            'formCode' => $doc === EvaluationFormCatalog::DOC_QMS_64 ? 'QMS-64' : 'QMS-63',
            'formTitle' => $doc === EvaluationFormCatalog::DOC_QMS_64
                ? 'Technical Findings'
                : 'Evaluation Sheet',
            'findings' => $findings,
            'items' => $doc === EvaluationFormCatalog::DOC_QMS_64
                ? $findings['technical']
                : $findings['completeness'],
        ]);
    }
}
