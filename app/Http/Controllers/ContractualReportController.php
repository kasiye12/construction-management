<?php

namespace App\Http\Controllers;

use App\Models\ContractualReport;
use App\Models\ContractualClaimCheck;
use App\Models\ContractualClaimEvent;
use App\Models\ContractualCorrespondenceCheck;
use App\Models\Project;
use App\Models\Document;
use App\Models\Notification;
use App\Models\WorkflowPermission;
use App\Services\ContractualReportExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContractualReportController extends Controller
{
    protected ContractualReportExcelService $excelService;

    public function __construct(ContractualReportExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    private function getUserProjectIds(): array
    {
        $user = Auth::user();
        if (!$user) return [];
        if ($user->isAdmin() || $user->is_super_admin) {
            return Project::withTrashed()->pluck('id')->toArray();
        }
        return $user->projects()
            ->withTrashed()
            ->where('project_user.is_active', true)
            ->pluck('projects.id')
            ->toArray();
    }

    private function getUserProjects()
    {
        $user = Auth::user();
        if (!$user) return collect();
        if ($user->isAdmin() || $user->is_super_admin) {
            return Project::all();
        }
        $ids = $this->getUserProjectIds();
        return Project::whereIn('id', $ids)->get();
    }

    // ==========================================
    // INDEX
    // ==========================================
    public function index(Request $request)
    {
        $userProjectIds = $this->getUserProjectIds();
        $projects = $this->getUserProjects();
        $projectId = $request->get('project_id');

        $query = ContractualReport::with(['project', 'preparedByUser', 'checkedByUser', 'approvedByUser'])
            ->whereIn('project_id', $userProjectIds);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('week_start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('week_end_date', '<=', $request->date_to);
        }
        if ($request->filled('further_action')) {
            $fa = strtolower($request->further_action);
            if ($fa === 'yes' || $fa === '1') {
                $query->where('needs_further_action', true);
            } elseif ($fa === 'no' || $fa === '0') {
                $query->where(function ($q) {
                    $q->where('needs_further_action', false)
                      ->orWhereNull('needs_further_action');
                });
            }
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('report_number', 'LIKE', "%{$s}%")
                  ->orWhere('project_location', 'LIKE', "%{$s}%")
                  ->orWhere('client_name', 'LIKE', "%{$s}%")
                  ->orWhere('consultant_name', 'LIKE', "%{$s}%")
                  ->orWhere('further_action_notes', 'LIKE', "%{$s}%");
            });
        }

        $reports = $query->orderBy('week_start_date', 'desc')->paginate(15)->appends($request->query());

        // Quick Stats
        $baseQuery = ContractualReport::whereIn('project_id', $userProjectIds);
        if ($projectId) $baseQuery->where('project_id', $projectId);

        $totalReports = (clone $baseQuery)->count();
        $approvedCount = (clone $baseQuery)->where('status', 'approved')->count();
        $pendingCount = (clone $baseQuery)->whereIn('status', ['submitted', 'checked'])->count();
        $draftCount = (clone $baseQuery)->where('status', 'draft')->count();
        $furtherActionCount = (clone $baseQuery)->where('needs_further_action', true)->count();

        return view('contractual-reports.index', compact(
            'reports', 'projects', 'projectId', 'totalReports',
            'approvedCount', 'pendingCount', 'draftCount', 'furtherActionCount'
        ));
    }

    // ==========================================
    // CREATE
    // ==========================================
    public function create(Request $request)
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin() && !$user->isManager() && !$user->hasPermission('contractual-reports.create') && !WorkflowPermission::canUserAct($user->id, 'prepare_contractual_report')) {
            return redirect()->route('contractual-reports.index')
                ->with('error', 'You do not have workflow permission to prepare contractual reports.');
        }

        $projects = $this->getUserProjects();
        if ($projects->isEmpty()) {
            return redirect()->route('contractual-reports.index')
                ->with('error', 'You do not have any assigned projects to prepare reports for.');
        }

        $userProjectIds = $this->getUserProjectIds();
        $projectId = $request->get('project_id', $projects->first()?->id);
        if (!$user->isAdmin() && !$user->is_super_admin && !in_array($projectId, $userProjectIds)) {
            $projectId = $projects->first()?->id;
        }
        $selectedProject = $projectId ? Project::find($projectId) : null;

        // Default week: Monday to Sunday of current week
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        // Preview report number
        $year = date('Y');
        $previewNumber = ContractualReport::generateNextReportNumber($year);

        $claimCategories = ContractualReport::CLAIM_CATEGORIES;
        $correspondenceCategories = ContractualReport::CORRESPONDENCE_CATEGORIES;

        return view('contractual-reports.create', compact(
            'projects', 'projectId', 'selectedProject', 'startOfWeek', 'endOfWeek',
            'previewNumber', 'claimCategories', 'correspondenceCategories'
        ));
    }

    // ==========================================
    // STORE
    // ==========================================
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin() && !$user->isManager() && !$user->hasPermission('contractual-reports.create') && !WorkflowPermission::canUserAct($user->id, 'prepare_contractual_report')) {
            return redirect()->route('contractual-reports.index')
                ->with('error', 'You do not have workflow permission to prepare contractual reports.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'week_start_date' => 'required|date',
            'week_end_date' => 'required|date|after_or_equal:week_start_date',
            'project_location' => 'nullable|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'consultant_name' => 'nullable|string|max:255',
            'contractor_name' => 'nullable|string|max:255',
            'document_code' => 'nullable|string|max:50',
            'issue_no' => 'nullable|string|max:20',
            'page1_notes' => 'nullable|string',
            'page2_remark' => 'nullable|string',
            'page3_remark' => 'nullable|string',
            'needs_further_action' => 'nullable|in:0,1,true,false',
            'further_action_notes' => 'nullable|string|max:1000',
            'claims' => 'nullable|array',
            'custom_claims' => 'nullable|array',
            'custom_claims.*.category' => 'nullable|string|max:100',
            'custom_claims.*.category_title' => 'nullable|string|max:255',
            'custom_claims.*.item_code' => 'nullable|string|max:50',
            'custom_claims.*.item_title' => 'nullable|string|max:255',
            'events' => 'nullable|array',
            'correspondences' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $userProjectIds = $this->getUserProjectIds();
        if (!$user->isAdmin() && !$user->is_super_admin && !in_array($validated['project_id'], $userProjectIds)) {
            return back()->withInput()->with('error', 'Unauthorized: You are not assigned to this project.');
        }

        $project = Project::findOrFail($validated['project_id']);

        // Generate report number
        $year = Carbon::parse($validated['week_start_date'])->format('Y');
        $reportNumber = ContractualReport::generateNextReportNumber($year);

        DB::beginTransaction();
        try {
            $report = ContractualReport::create([
                'project_id' => $project->id,
                'report_number' => $reportNumber,
                'document_code' => $validated['document_code'] ?? 'OF/TNT/CAD/01',
                'issue_no' => $validated['issue_no'] ?? '1',
                'project_location' => $validated['project_location'] ?? ($project->location ?? 'Site'),
                'client_name' => $validated['client_name'] ?? $project->client_name,
                'consultant_name' => $validated['consultant_name'] ?? $project->consultant_name,
                'contractor_name' => $validated['contractor_name'] ?? 'TNT CONSTRUCTION AND TRADING',
                'week_start_date' => $validated['week_start_date'],
                'week_end_date' => $validated['week_end_date'],
                'status' => 'draft',
                'prepared_by' => Auth::id(),
                'prepared_name' => Auth::user()?->name,
                'prepared_at' => now(),
                'page1_notes' => $validated['page1_notes'] ?? null,
                'page2_remark' => $validated['page2_remark'] ?? null,
                'page3_remark' => $validated['page3_remark'] ?? null,
                'needs_further_action' => filter_var($request->input('needs_further_action', false), FILTER_VALIDATE_BOOLEAN),
                'further_action_notes' => $request->input('further_action_notes'),
                'created_by' => Auth::id(),
            ]);

            // Save Claim Checks (Page 1)
            $claimCategories = ContractualReport::CLAIM_CATEGORIES;
            $sort = 1;
            $claimsInput = $request->input('claims', []);

            foreach ($claimCategories as $catKey => $catData) {
                foreach ($catData['items'] as $code => $title) {
                    $itemInput = $claimsInput[$code] ?? [];
                    ContractualClaimCheck::create([
                        'contractual_report_id' => $report->id,
                        'category' => $catKey,
                        'category_title' => $catData['title'],
                        'item_code' => $code,
                        'item_title' => $title,
                        'monday' => !empty($itemInput['monday']),
                        'tuesday' => !empty($itemInput['tuesday']),
                        'wednesday' => !empty($itemInput['wednesday']),
                        'thursday' => !empty($itemInput['thursday']),
                        'friday' => !empty($itemInput['friday']),
                        'saturday' => !empty($itemInput['saturday']),
                        'sunday' => !empty($itemInput['sunday']),
                        'sort_order' => $sort++,
                    ]);
                }
            }

            // Save Custom Claim Checks (Page 1)
            $customClaims = $request->input('custom_claims', []);
            if (is_array($customClaims)) {
                foreach ($customClaims as $custom) {
                    if (empty($custom['item_title'])) continue;
                    $code = !empty($custom['item_code']) ? trim($custom['item_code']) : ('C-' . $sort);
                    $catTitle = !empty($custom['category_title']) ? trim($custom['category_title']) : 'Other Contractual Causes';
                    $catKey = !empty($custom['category']) ? trim($custom['category']) : \Illuminate\Support\Str::slug($catTitle, '_');

                    ContractualClaimCheck::create([
                        'contractual_report_id' => $report->id,
                        'category' => $catKey,
                        'category_title' => $catTitle,
                        'item_code' => $code,
                        'item_title' => trim($custom['item_title']),
                        'monday' => !empty($custom['monday']),
                        'tuesday' => !empty($custom['tuesday']),
                        'wednesday' => !empty($custom['wednesday']),
                        'thursday' => !empty($custom['thursday']),
                        'friday' => !empty($custom['friday']),
                        'saturday' => !empty($custom['saturday']),
                        'sunday' => !empty($custom['sunday']),
                        'sort_order' => $sort++,
                    ]);
                }
            }

            // Save Claim Events (Page 2)
            $eventsInput = $request->input('events', []);
            $eventOrder = 1;
            foreach ($eventsInput as $evt) {
                if (!empty($evt['issue_number']) || !empty($evt['cause_description'])) {
                    ContractualClaimEvent::create([
                        'contractual_report_id' => $report->id,
                        'event_date' => !empty($evt['event_date']) ? $evt['event_date'] : $report->week_start_date,
                        'issue_number' => $evt['issue_number'] ?? null,
                        'cause_description' => $evt['cause_description'] ?? null,
                        'effect_on_progress' => $evt['effect_on_progress'] ?? null,
                        'action_taken' => $evt['action_taken'] ?? null,
                        'remark' => $evt['remark'] ?? null,
                        'sort_order' => $eventOrder++,
                    ]);
                }
            }

            // Save Correspondence Checks (Page 3)
            $correspCategories = ContractualReport::CORRESPONDENCE_CATEGORIES;
            $cSort = 1;
            $correspInput = $request->input('correspondences', []);

            foreach ($correspCategories as $dirKey => $dirData) {
                foreach ($dirData['items'] as $code => $party) {
                    $inputKey = $dirKey . '_' . str_replace('.', '_', $code);
                    $cInput = $correspInput[$inputKey] ?? [];
                    ContractualCorrespondenceCheck::create([
                        'contractual_report_id' => $report->id,
                        'direction' => $dirKey,
                        'direction_title' => $dirData['title'],
                        'item_code' => $code,
                        'party_title' => $party,
                        'monday' => !empty($cInput['monday']),
                        'tuesday' => !empty($cInput['tuesday']),
                        'wednesday' => !empty($cInput['wednesday']),
                        'thursday' => !empty($cInput['thursday']),
                        'friday' => !empty($cInput['friday']),
                        'saturday' => !empty($cInput['saturday']),
                        'sunday' => !empty($cInput['sunday']),
                        'sort_order' => $cSort++,
                    ]);
                }
            }

            // Upload Attachments (Letters & Claim Documents)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('documents/contractual/' . date('Y/m'), 'public');
                    Document::create([
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'documentable_type' => ContractualReport::class,
                        'documentable_id' => $report->id,
                        'uploaded_by' => Auth::id(),
                        'description' => 'Attached Letter / Claim Evidence for Report ' . $report->report_number,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('contractual-reports.show', $report)
                ->with('success', 'Weekly Contractual Follow-up Report created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create report: ' . $e->getMessage());
        }
    }

    // ==========================================
    // SHOW
    // ==========================================
    public function show(ContractualReport $contractualReport)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        if (!$user->isAdmin() && !$user->is_super_admin && !in_array($contractualReport->project_id, $userProjectIds)) {
            abort(403, 'Unauthorized access to this project report.');
        }

        $contractualReport->load([
            'project',
            'claimChecks',
            'claimEvents',
            'correspondenceChecks',
            'preparedByUser',
            'checkedByUser',
            'approvedByUser',
            'documents.uploader',
        ]);

        $user = Auth::user();
        $canSubmit = $user && ($user->isAdmin() || $user->isManager() || $user->hasPermission('contractual-reports.create') || $user->role === 'Contract' || WorkflowPermission::canUserAct($user->id, 'prepare_contractual_report') || $contractualReport->prepared_by === $user->id);
        $canCheck = $user && ($user->isAdmin() || $user->isManager() || WorkflowPermission::canUserAct($user->id, 'check_contractual_report'));
        $canApprove = $user && ($user->isAdmin() || $user->isManager() || WorkflowPermission::canUserAct($user->id, 'approve_contractual_report'));
        $canReject = $user && ($user->isAdmin() || $user->isManager() || WorkflowPermission::canUserAct($user->id, 'reject_contractual_report') || WorkflowPermission::canUserAct($user->id, 'check_contractual_report'));

        return view('contractual-reports.show', compact('contractualReport', 'canSubmit', 'canCheck', 'canApprove', 'canReject'));
    }

    // ==========================================
    // EDIT
    // ==========================================
    public function edit(ContractualReport $contractualReport)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        if (!$user->isAdmin() && !$user->is_super_admin && !in_array($contractualReport->project_id, $userProjectIds)) {
            abort(403, 'Unauthorized access to this project report.');
        }

        $canEdit = $user && ($user->isAdmin() || $user->isManager() || $user->hasPermission('contractual-reports.edit') || $contractualReport->prepared_by === $user->id || $user->role === 'Contract');
        if ((!$contractualReport->isDraft() && !$contractualReport->isRejected()) || !$canEdit) {
            return redirect()->route('contractual-reports.show', $contractualReport)
                ->with('error', 'Only draft or rejected reports can be edited by authorized users.');
        }

        $contractualReport->load(['project', 'claimChecks', 'claimEvents', 'correspondenceChecks', 'documents']);
        $projects = $this->getUserProjects();
        $claimCategories = ContractualReport::CLAIM_CATEGORIES;
        $correspondenceCategories = ContractualReport::CORRESPONDENCE_CATEGORIES;

        // Separate standard and custom claim checks
        $standardCodes = [];
        foreach ($claimCategories as $cat) {
            foreach (array_keys($cat['items']) as $code) {
                $standardCodes[] = (string)$code;
            }
        }
        $customClaimChecks = $contractualReport->claimChecks->filter(function ($chk) use ($standardCodes) {
            return !in_array((string)$chk->item_code, $standardCodes);
        });

        return view('contractual-reports.edit', compact(
            'contractualReport', 'projects', 'claimCategories', 'correspondenceCategories', 'customClaimChecks'
        ));
    }

    // ==========================================
    // UPDATE
    // ==========================================
    public function update(Request $request, ContractualReport $contractualReport)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        if (!$user->isAdmin() && !$user->is_super_admin && !in_array($contractualReport->project_id, $userProjectIds)) {
            abort(403, 'Unauthorized access to this project report.');
        }

        if (!$contractualReport->isDraft() && !$contractualReport->isRejected() && !Auth::user()->isAdmin()) {
            return redirect()->route('contractual-reports.show', $contractualReport)
                ->with('error', 'Only draft or rejected reports can be updated.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'week_start_date' => 'required|date',
            'week_end_date' => 'required|date|after_or_equal:week_start_date',
            'project_location' => 'nullable|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'consultant_name' => 'nullable|string|max:255',
            'contractor_name' => 'nullable|string|max:255',
            'document_code' => 'nullable|string|max:50',
            'issue_no' => 'nullable|string|max:20',
            'page1_notes' => 'nullable|string',
            'page2_remark' => 'nullable|string',
            'page3_remark' => 'nullable|string',
            'needs_further_action' => 'nullable|in:0,1,true,false',
            'further_action_notes' => 'nullable|string|max:1000',
            'claims' => 'nullable|array',
            'custom_claims' => 'nullable|array',
            'custom_claims.*.category' => 'nullable|string|max:100',
            'custom_claims.*.category_title' => 'nullable|string|max:255',
            'custom_claims.*.item_code' => 'nullable|string|max:50',
            'custom_claims.*.item_title' => 'nullable|string|max:255',
            'events' => 'nullable|array',
            'correspondences' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $contractualReport->update([
                'project_id' => $validated['project_id'],
                'document_code' => $validated['document_code'] ?? 'OF/TNT/CAD/01',
                'issue_no' => $validated['issue_no'] ?? '1',
                'project_location' => $validated['project_location'] ?? $contractualReport->project_location,
                'client_name' => $validated['client_name'] ?? $contractualReport->client_name,
                'consultant_name' => $validated['consultant_name'] ?? $contractualReport->consultant_name,
                'contractor_name' => $validated['contractor_name'] ?? 'TNT CONSTRUCTION AND TRADING',
                'week_start_date' => $validated['week_start_date'],
                'week_end_date' => $validated['week_end_date'],
                'page1_notes' => $validated['page1_notes'] ?? null,
                'page2_remark' => $validated['page2_remark'] ?? null,
                'page3_remark' => $validated['page3_remark'] ?? null,
                'needs_further_action' => filter_var($request->input('needs_further_action', false), FILTER_VALIDATE_BOOLEAN),
                'further_action_notes' => $request->input('further_action_notes'),
            ]);

            // Update Standard Claim Checks
            $claimCategories = ContractualReport::CLAIM_CATEGORIES;
            $standardCodes = [];
            foreach ($claimCategories as $c) {
                foreach (array_keys($c['items']) as $code) {
                    $standardCodes[] = (string)$code;
                }
            }

            $claimsInput = $request->input('claims', []);
            $claimChecks = $contractualReport->claimChecks()->get()->keyBy('item_code');
            foreach ($standardCodes as $code) {
                $check = $claimChecks->get($code);
                $itemInput = $claimsInput[$code] ?? [];
                if ($check) {
                    $check->update([
                        'monday' => !empty($itemInput['monday']),
                        'tuesday' => !empty($itemInput['tuesday']),
                        'wednesday' => !empty($itemInput['wednesday']),
                        'thursday' => !empty($itemInput['thursday']),
                        'friday' => !empty($itemInput['friday']),
                        'saturday' => !empty($itemInput['saturday']),
                        'sunday' => !empty($itemInput['sunday']),
                    ]);
                }
            }

            // Sync Custom Claim Checks (delete non-standard and recreate from custom_claims)
            $contractualReport->claimChecks()->whereNotIn('item_code', $standardCodes)->delete();
            $customClaims = $request->input('custom_claims', []);
            $cSort = 50;
            if (is_array($customClaims)) {
                foreach ($customClaims as $custom) {
                    if (empty($custom['item_title'])) continue;
                    $code = !empty($custom['item_code']) ? trim($custom['item_code']) : ('C-' . $cSort);
                    $catTitle = !empty($custom['category_title']) ? trim($custom['category_title']) : 'Other Contractual Causes';
                    $catKey = !empty($custom['category']) ? trim($custom['category']) : \Illuminate\Support\Str::slug($catTitle, '_');

                    ContractualClaimCheck::create([
                        'contractual_report_id' => $contractualReport->id,
                        'category' => $catKey,
                        'category_title' => $catTitle,
                        'item_code' => $code,
                        'item_title' => trim($custom['item_title']),
                        'monday' => !empty($custom['monday']),
                        'tuesday' => !empty($custom['tuesday']),
                        'wednesday' => !empty($custom['wednesday']),
                        'thursday' => !empty($custom['thursday']),
                        'friday' => !empty($custom['friday']),
                        'saturday' => !empty($custom['saturday']),
                        'sunday' => !empty($custom['sunday']),
                        'sort_order' => $cSort++,
                    ]);
                }
            }

            // Sync Claim Events (delete old and re-create)
            $contractualReport->claimEvents()->delete();
            $eventsInput = $request->input('events', []);
            $eventOrder = 1;
            foreach ($eventsInput as $evt) {
                if (!empty($evt['issue_number']) || !empty($evt['cause_description'])) {
                    ContractualClaimEvent::create([
                        'contractual_report_id' => $contractualReport->id,
                        'event_date' => !empty($evt['event_date']) ? $evt['event_date'] : $contractualReport->week_start_date,
                        'issue_number' => $evt['issue_number'] ?? null,
                        'cause_description' => $evt['cause_description'] ?? null,
                        'effect_on_progress' => $evt['effect_on_progress'] ?? null,
                        'action_taken' => $evt['action_taken'] ?? null,
                        'remark' => $evt['remark'] ?? null,
                        'sort_order' => $eventOrder++,
                    ]);
                }
            }

            // Update Correspondence Checks
            $correspInput = $request->input('correspondences', []);
            $correspChecks = $contractualReport->correspondenceChecks()->get();
            foreach ($correspChecks as $check) {
                $inputKey = $check->direction . '_' . str_replace('.', '_', $check->item_code);
                $cInput = $correspInput[$inputKey] ?? [];
                $check->update([
                    'monday' => !empty($cInput['monday']),
                    'tuesday' => !empty($cInput['tuesday']),
                    'wednesday' => !empty($cInput['wednesday']),
                    'thursday' => !empty($cInput['thursday']),
                    'friday' => !empty($cInput['friday']),
                    'saturday' => !empty($cInput['saturday']),
                    'sunday' => !empty($cInput['sunday']),
                ]);
            }

            // Upload Attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('documents/contractual/' . date('Y/m'), 'public');
                    Document::create([
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'documentable_type' => ContractualReport::class,
                        'documentable_id' => $contractualReport->id,
                        'uploaded_by' => Auth::id(),
                        'description' => 'Attached Letter / Claim Evidence for Report ' . $contractualReport->report_number,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('contractual-reports.show', $contractualReport)
                ->with('success', 'Weekly Contractual Follow-up Report updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update report: ' . $e->getMessage());
        }
    }

    // ==========================================
    // DESTROY / REMOVE FROM LIST
    // ==========================================
    public function destroy(ContractualReport $contractualReport)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $userProjectIds = $this->getUserProjectIds();
        if (!$user->isAdmin() && !$user->is_super_admin && !in_array($contractualReport->project_id, $userProjectIds)) {
            abort(403, 'Unauthorized access to this project report.');
        }

        // 1. Strict permission check: Must have contractual-reports.delete permission or be Admin
        $hasPermission = $user->isAdmin() || $user->is_super_admin || $user->hasPermission('contractual-reports.delete');
        if (!$hasPermission) {
            return back()->with('error', 'Unauthorized: You do not have permission to delete contractual reports.');
        }

        // 2. Strict status check: Reports that are submitted, checked, or approved CANNOT be deleted
        if (!$contractualReport->isDraft() && !$contractualReport->isRejected()) {
            $statusLabel = ucfirst($contractualReport->status);
            return back()->with('error', "Deletion blocked: Cannot delete a {$statusLabel} report. Only reports in 'Draft' status can be removed from the system.");
        }

        $reportNum = $contractualReport->report_number;
        $contractualReport->delete();

        return redirect()->route('contractual-reports.index')
            ->with('success', "Contractual report {$reportNum} has been removed successfully.");
    }

    // ==========================================
    // WORKFLOW TRANSITIONS
    // ==========================================
    public function submit(ContractualReport $contractualReport)
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin() && !$user->isManager() && !WorkflowPermission::canUserAct($user->id, 'prepare_contractual_report') && $contractualReport->prepared_by !== $user->id) {
            return back()->with('error', 'You do not have workflow permission to submit this report.');
        }

        $contractualReport->update([
            'status' => 'submitted',
            'prepared_by' => Auth::id(),
            'prepared_name' => Auth::user()?->name,
            'prepared_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Report submitted for review.');
    }

    public function check(ContractualReport $contractualReport)
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin() && !$user->isManager() && !WorkflowPermission::canUserAct($user->id, 'check_contractual_report')) {
            return back()->with('error', 'You do not have workflow permission to verify/check contractual reports.');
        }

        $contractualReport->update([
            'status' => 'checked',
            'checked_by' => Auth::id(),
            'checked_name' => Auth::user()?->name,
            'checked_at' => now(),
        ]);

        return back()->with('success', 'Report verified and signed by reviewer.');
    }

    public function approve(ContractualReport $contractualReport)
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin() && !$user->isManager() && !WorkflowPermission::canUserAct($user->id, 'approve_contractual_report')) {
            return back()->with('error', 'You do not have workflow permission to approve contractual reports.');
        }

        $contractualReport->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_name' => Auth::user()?->name,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Report officially approved.');
    }

    public function reject(Request $request, ContractualReport $contractualReport)
    {
        $user = Auth::user();
        if ($user && !$user->isAdmin() && !$user->isManager() && !WorkflowPermission::canUserAct($user->id, 'reject_contractual_report') && !WorkflowPermission::canUserAct($user->id, 'check_contractual_report')) {
            return back()->with('error', 'You do not have workflow permission to reject contractual reports.');
        }

        $request->validate(['reason' => 'required|string|max:1000']);

        $contractualReport->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', 'Report rejected back to preparer.');
    }

    public function revert(ContractualReport $contractualReport)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->with('error', 'Only administrators can revert reports.');
        }

        $contractualReport->update([
            'status' => 'draft',
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Report reverted to draft.');
    }

    public function toggleFurtherAction(Request $request, ContractualReport $contractualReport)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        if (!$user->isAdmin() && !$user->is_super_admin && !in_array($contractualReport->project_id, $userProjectIds)) {
            abort(403, 'Unauthorized access to this project report.');
        }

        $request->validate([
            'needs_further_action' => 'required|in:0,1,true,false',
            'further_action_notes' => 'nullable|string|max:1000',
        ]);

        $needsAction = filter_var($request->input('needs_further_action'), FILTER_VALIDATE_BOOLEAN);

        $contractualReport->update([
            'needs_further_action' => $needsAction,
            'further_action_notes' => $needsAction ? ($request->input('further_action_notes') ?? $contractualReport->further_action_notes) : null,
        ]);

        $statusText = $needsAction ? 'marked as requiring Further Action (Yes)' : 'marked as No Further Action (No)';
        return back()->with('success', "Report {$contractualReport->report_number} {$statusText}.");
    }

    // ==========================================
    // EXPORT EXCEL
    // ==========================================
    public function exportExcel(ContractualReport $contractualReport)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        if (!$user->isAdmin() && !$user->is_super_admin && !in_array($contractualReport->project_id, $userProjectIds)) {
            abort(403, 'Unauthorized access to this project report.');
        }

        try {
            $filePath = $this->excelService->export($contractualReport);
            $filename = 'Weekly_Contractual_Report_' . str_replace('/', '-', $contractualReport->report_number) . '_' . date('Ymd') . '.xlsx';
            return response()->download($filePath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Excel export failed: ' . $e->getMessage());
        }
    }

    // ==========================================
    // DOWNLOAD BLANK TEMPLATE
    // ==========================================
    public function downloadTemplate()
    {
        $path = $this->excelService->getTemplatePath();
        if (!file_exists($path)) {
            return back()->with('error', 'Master template file not found.');
        }
        return response()->download($path, 'TNT-OF-CAD-01-Weekly-Contractual-Follow-up-Reporting-Form.xlsx');
    }

    // ==========================================
    // IMPORT EXCEL
    // ==========================================
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'project_id' => 'required|exists:projects,id',
        ]);

        try {
            $report = $this->excelService->import(
                $request->file('file'),
                (int)$request->project_id,
                Auth::id()
            );

            return redirect()->route('contractual-reports.show', $report)
                ->with('success', 'Excel file imported successfully as ' . $report->report_number);
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ==========================================
    // PRINT / PDF VIEW
    // ==========================================
    public function print(ContractualReport $contractualReport)
    {
        $user = Auth::user();
        $userProjectIds = $this->getUserProjectIds();
        if (!$user->isAdmin() && !$user->is_super_admin && !in_array($contractualReport->project_id, $userProjectIds)) {
            abort(403, 'Unauthorized access to this project report.');
        }

        $contractualReport->load([
            'project',
            'claimChecks',
            'claimEvents',
            'correspondenceChecks',
            'preparedByUser',
            'checkedByUser',
            'approvedByUser',
            'documents',
        ]);

        return view('contractual-reports.print', compact('contractualReport'));
    }
}
