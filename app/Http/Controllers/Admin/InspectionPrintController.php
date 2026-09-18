<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Support\Inspection\InspectionFormCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InspectionPrintController extends Controller
{
    /**
     * Government-letterhead print for QMS-38/39, O-03, QMS-65, or DPWH 77-006-E.
     */
    public function __invoke(Request $request, Inspection $inspection): View
    {
        $doc = strtolower((string) $request->query('doc', InspectionFormCatalog::DOC_QMS_65));
        if (! in_array($doc, InspectionFormCatalog::documentCodes(), true)) {
            throw new NotFoundHttpException('Unknown inspection form document.');
        }

        $inspection->load(['application', 'inspector', 'scheduledByUser']);

        $meta = match ($doc) {
            InspectionFormCatalog::DOC_QMS_38 => [
                'formCode' => 'QMS-38',
                'formTitle' => 'Joint Inspection Schedule',
            ],
            InspectionFormCatalog::DOC_QMS_39 => [
                'formCode' => 'QMS-39',
                'formTitle' => 'Joint Inspection Team Assignment',
            ],
            InspectionFormCatalog::DOC_O_03 => [
                'formCode' => 'O-03',
                'formTitle' => 'Individual Inspector Notes',
            ],
            InspectionFormCatalog::DOC_DPWH_77_006_E => [
                'formCode' => '77-006-E',
                'formTitle' => 'Final Electrical Inspection',
            ],
            default => [
                'formCode' => 'QMS-65',
                'formTitle' => 'Inspection Compliance Sheet',
            ],
        };

        return view('admin.inspection-print', [
            'inspection' => $inspection,
            'doc' => $doc,
            'formCode' => $meta['formCode'],
            'formTitle' => $meta['formTitle'],
            'scheduleSheet' => is_array($inspection->schedule_sheet) ? $inspection->schedule_sheet : [],
            'team' => InspectionFormCatalog::normalizeTeamInspectors($inspection->team_inspectors),
            'inspectorNotes' => InspectionFormCatalog::normalizeInspectorNotes($inspection->inspector_notes),
            'compliance' => InspectionFormCatalog::normalizeComplianceSheet($inspection->compliance_sheet),
            'electrical' => InspectionFormCatalog::normalizeElectricalForm($inspection->electrical_form),
        ]);
    }
}
