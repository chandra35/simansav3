<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmailTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-settings');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Debug logging
        Log::info('EmailTemplate index called', [
            'has_draw' => $request->has('draw'),
            'draw' => $request->get('draw'),
            'is_ajax' => $request->ajax(),
            'all_params' => $request->all()
        ]);

        // Check if DataTables request
        if ($request->has('draw')) {
            Log::info('DataTables request detected');
            $query = EmailTemplate::query();

            // Filter by status
            if ($request->filled('status')) {
                $query->where('is_active', $request->status == '1');
            }

            // Filter by type
            if ($request->filled('type')) {
                $query->where('is_system', $request->type == 'system');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('is_active_badge', function ($row) {
                    return $row->is_active 
                        ? '<span class="badge badge-success">Aktif</span>' 
                        : '<span class="badge badge-danger">Nonaktif</span>';
                })
                ->addColumn('is_system_badge', function ($row) {
                    return $row->is_system 
                        ? '<span class="badge badge-primary">Sistem</span>' 
                        : '<span class="badge badge-secondary">Custom</span>';
                })
                ->addColumn('updated_at_formatted', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('d M Y H:i') : '-';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group btn-group-sm">';
                    $buttons .= '<button type="button" class="btn btn-info btn-preview" data-id="' . $row->id . '" title="Preview"><i class="fas fa-eye"></i></button>';
                    $buttons .= '<a href="' . route('admin.email-templates.edit', $row->id) . '" class="btn btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    $buttons .= '<button type="button" class="btn btn-secondary btn-duplicate" data-id="' . $row->id . '" title="Duplikasi"><i class="fas fa-copy"></i></button>';
                    if (!$row->is_system) {
                        $buttons .= '<button type="button" class="btn btn-danger btn-delete" data-id="' . $row->id . '" data-name="' . $row->name . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    }
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['is_active_badge', 'is_system_badge', 'action'])
                ->make(true);
        }

        // Statistics
        $stats = [
            'total' => EmailTemplate::count(),
            'active' => EmailTemplate::where('is_active', true)->count(),
            'inactive' => EmailTemplate::where('is_active', false)->count(),
            'system' => EmailTemplate::where('is_system', true)->count(),
            'custom' => EmailTemplate::where('is_system', false)->count(),
        ];

        return view('admin.email-templates.index', compact('stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $placeholders = EmailTemplate::getPlaceholderDefinitions();
        return view('admin.email-templates.create', compact('placeholders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:email_templates,code|regex:/^[a-z0-9_]+$/',
            'name' => 'required|string|max:100',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'description' => 'nullable|string|max:500',
            'available_placeholders' => 'nullable|array',
            'is_active' => 'boolean',
        ], [
            'code.regex' => 'Kode hanya boleh berisi huruf kecil, angka, dan underscore.',
            'code.unique' => 'Kode template sudah digunakan.',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');
        $validated['is_system'] = false;

        EmailTemplate::create($validated);

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return response()->json([
            'success' => true,
            'data' => $emailTemplate,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        $placeholders = EmailTemplate::getPlaceholderDefinitions();
        return view('admin.email-templates.edit', compact('emailTemplate', 'placeholders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('email_templates', 'code')->ignore($emailTemplate->id),
            ],
            'name' => 'required|string|max:100',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'description' => 'nullable|string|max:500',
            'available_placeholders' => 'nullable|array',
            'is_active' => 'boolean',
        ], [
            'code.regex' => 'Kode hanya boleh berisi huruf kecil, angka, dan underscore.',
            'code.unique' => 'Kode template sudah digunakan.',
        ]);

        $validated['updated_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');

        // Don't allow changing system flag
        unset($validated['is_system']);

        $emailTemplate->update($validated);

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        if ($emailTemplate->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Template sistem tidak dapat dihapus.',
            ], 403);
        }

        $emailTemplate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template email berhasil dihapus.',
        ]);
    }

    /**
     * Preview template with sample data
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        $rendered = $emailTemplate->preview();
        
        return response()->json([
            'success' => true,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
        ]);
    }

    /**
     * Preview template from form data (before saving)
     */
    public function previewForm(Request $request)
    {
        $template = new EmailTemplate([
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        $rendered = $template->preview();
        
        return response()->json([
            'success' => true,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
        ]);
    }

    /**
     * Duplicate a template
     */
    public function duplicate(EmailTemplate $emailTemplate)
    {
        $newTemplate = $emailTemplate->replicate();
        $newTemplate->code = $emailTemplate->code . '_copy_' . time();
        $newTemplate->name = $emailTemplate->name . ' (Copy)';
        $newTemplate->is_system = false;
        $newTemplate->created_by = Auth::id();
        $newTemplate->updated_by = Auth::id();
        $newTemplate->save();

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil diduplikasi.',
            'redirect' => route('admin.email-templates.edit', $newTemplate),
        ]);
    }

    /**
     * Toggle template status
     */
    public function toggleStatus(EmailTemplate $emailTemplate)
    {
        $emailTemplate->update([
            'is_active' => !$emailTemplate->is_active,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status template berhasil diubah.',
            'is_active' => $emailTemplate->is_active,
        ]);
    }

    /**
     * Seed default templates
     */
    public function seedDefaults()
    {
        EmailTemplate::seedDefaults();

        return response()->json([
            'success' => true,
            'message' => 'Template default berhasil dimuat.',
        ]);
    }

    /**
     * Reset template to default (system templates only)
     */
    public function resetToDefault(EmailTemplate $emailTemplate)
    {
        if (!$emailTemplate->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya template sistem yang dapat direset.',
            ], 403);
        }

        // Re-seed to get defaults
        EmailTemplate::seedDefaults();

        return response()->json([
            'success' => true,
            'message' => 'Template berhasil direset ke default.',
        ]);
    }
}
