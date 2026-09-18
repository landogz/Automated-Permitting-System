export type InspectionTeamMember = {
    name: string;
    role?: string;
    discipline?: string;
};

export type ComplianceItem = {
    code?: string;
    label?: string;
    item?: string;
    status?: string;
    remarks?: string;
    notes?: string;
};

export type ComplianceSheet = {
    form_code?: string;
    items?: ComplianceItem[];
    overall_remarks?: string;
};

export type InspectorNotes = {
    form_code?: string;
    weather?: string;
    site_conditions?: string;
    findings?: string;
    observed_defects?: string;
    recommendations?: string;
};

export type ElectricalForm = {
    form_code?: string;
    service_entrance?: string;
    grounding?: string;
    panel_boards?: string;
    wiring_methods?: string;
    fixtures_devices?: string;
    load_schedule?: string;
    remarks?: string;
    result?: string;
    status?: string;
};

export type InspectionPrintUrls = Partial<
    Record<'qms-38' | 'qms-39' | 'o-03' | 'qms-65' | 'dpwh-77-006-e', string>
>;

export type InspectionRow = {
    uuid: string;
    inspection_no: string;
    type: string;
    status: string;
    result?: string | null;
    scheduled_at?: string | null;
    completed_at?: string | null;
    location?: string | null;
    latitude?: number | null;
    longitude?: number | null;
    notes?: string | null;
    team_inspectors?: InspectionTeamMember[];
    schedule_sheet?: Record<string, unknown>;
    inspector_notes?: InspectorNotes;
    compliance_sheet?: ComplianceSheet | ComplianceItem[];
    electrical_form?: ElectricalForm;
    requires_electrical_form?: boolean;
    print_urls?: InspectionPrintUrls | null;
    application?: {
        uuid?: string;
        application_no?: string;
        project_title?: string;
        project_location?: string;
        latitude?: number | null;
        longitude?: number | null;
        status?: string;
    };
    inspector?: { uuid?: string; name?: string; avatar_url?: string | null };
};

export type InspectionTemplatesPayload = {
    qms65_default_items?: ComplianceItem[];
    electrical_blank?: ElectricalForm;
    inspector_notes_blank?: InspectorNotes;
};
