<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Forms;
use App\Models\User;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\FormSubmissionValue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class FormController extends Controller
{
    // Fetch forms for sidebar
    protected function sidebarForms()
    {
        return Forms::all();
    }

    /* -------------------------------------------------
     | Dashboard
     |--------------------------------------------------*/
    public function dashboard()
    {
        $forms = $this->sidebarForms();
        $Username = User::where('id', Auth::id())->value('name');

        return view('dashboard', compact('Username', 'forms'));
    }

    /* -------------------------------------------------
     | Forms CRUD
     |--------------------------------------------------*/
    public function index()
    {
        $forms = $this->sidebarForms();
        return view('forms.index', compact('forms'));
    }

    public function formsDatatable()
    {
        $forms = Forms::select(['id', 'name']);
        // dd($forms);
        return DataTables::of($forms)
            ->addColumn('action', function ($form) {
                $editUrl = route('forms.edit', $form->id);
                $deleteUrl = route('forms.destroy', $form->id);

                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
            <div class="dropdown">
                <button class="btn btn-sm p-0 border-0" type="button"
                        id="dropdownMenuButton' . $form->id . '" data-bs-toggle="dropdown"
                        aria-expanded="false">
                    <i class="bi bi-three-dots text-primary fs-5"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton' . $form->id . '">
                    <li>
                        <a class="dropdown-item text-primary" href="' . $editUrl . '">
                            <i class="bi bi-pencil me-2 text-primary"></i>Edit
                        </a>
                    </li>
                    <li>
                        <form action="' . $deleteUrl . '" method="POST" class="m-0 p-0">
                            ' . $csrf . '
                            ' . $method . '
                            <button class="dropdown-item text-danger delete-btn" type="submit">
                                <i class="bi bi-trash me-2"></i>Delete
                            </button>
                        </form>
                    </li>
                </ul>
            </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $forms = $this->sidebarForms();
        return view('forms.create', compact('forms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $form = Forms::create([
            'name' => $request->name
        ]);

        return redirect()
            ->route('forms.edit', $form->id)
            ->with('success', 'Form created successfully');
    }

    public function edit($id)
    {
        $form = Forms::with('fields')->findOrFail($id);
        $forms = $this->sidebarForms();

        return view('forms.edit', compact('form', 'forms'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $form = Forms::findOrFail($id);
        $form->update(['name' => $request->name]);

        return redirect()
            ->route('forms.index')
            ->with('success', 'Form updated successfully');
    }

    public function destroy($id)
    {
        Forms::findOrFail($id)->delete();
        return redirect()
            ->route('forms.index')
            ->with('success', 'Form deleted');
    }

    /* -------------------------------------------------
     | Form Fields
     |--------------------------------------------------*/
    public function addField(Request $request, $formId)
    {
        $request->validate([
            'label' => 'required',
            'type'  => 'required|in:text,number,textarea,date,time',
        ]);

        FormField::create([
            'form_id' => $formId,
            'label' => $request->label,
            'type' => $request->type,
            'is_required' => $request->is_required ?? 0
        ]);

        return back()->with('success', 'Field added');
    }

    public function updateField(Request $request, $id)
    {
        $request->validate([
            'label' => 'required',
            'type' => 'required|in:text,number,textarea,date,time'
        ]);

        FormField::findOrFail($id)->update([
            'label' => $request->label,
            'type' => $request->type,
            'is_required' => $request->is_required ?? 0
        ]);

        return back()->with('success', 'Field updated');
    }

    public function deleteField($id)
    {
        FormField::findOrFail($id)->delete();
        return back()->with('success', 'Field deleted');
    }

    /* -------------------------------------------------
     | Submissions
     |--------------------------------------------------*/
    public function submissions($formId)
    {
        $form = Forms::with('fields')->findOrFail($formId);
        $forms = $this->sidebarForms();

        return view('submissions.index', compact('form', 'forms'));
    }

    public function createSubmission($formId)
    {
        $form = Forms::with('fields')->findOrFail($formId);
        $forms = $this->sidebarForms();

        return view('submissions.create', compact('form', 'forms'));
    }

    public function storeSubmission(Request $request, $formId)
    {
        $form = Forms::with('fields')->findOrFail($formId);

        $rules = [];
        $attributes = [];

        foreach ($form->fields as $field) {
            $key = "fields.{$field->id}";
            $attributes[$key] = $field->label;

            if ($field->type === 'date') {
                $rules[$key] = ['required', 'date_format:d/m/Y'];
                continue;
            }

            if ($field->type === 'time') {
                $rules[$key] = [
                    'required',
                    'regex:/^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)$/i'
                ];
                continue;
            }

            if ($field->is_required) {
                $rules[$key] = 'required';
            }
        }

        $request->validate($rules, ['required' => ':attribute is required.'], $attributes);

        $submission = FormSubmission::create(['form_id' => $formId]);

        foreach ($request->input('fields', []) as $fieldId => $value) {
            $field = $form->fields->firstWhere('id', $fieldId);
            if (!$field) continue;

            if ($field->type === 'date') {
                $value = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            if ($field->type === 'time') {
                $value = Carbon::createFromFormat('h:i A', $value)->format('H:i:s');
            }

            FormSubmissionValue::create([
                'submission_id' => $submission->id,
                'field_id' => $fieldId,
                'value' => $value
            ]);
        }

        return redirect()
            ->route('forms.submissions', $formId)
            ->with('success', 'Submission saved successfully!');
    }

    public function editSubmission($id)
    {
        $submission = FormSubmission::with(['values', 'form.fields'])
            ->findOrFail($id);

        $form = $submission->form;
        $forms = $this->sidebarForms();

        $values = $submission->values
            ->pluck('value', 'field_id')
            ->toArray();

        return view('submissions.edit', compact('form', 'submission', 'values', 'forms'));
    }

    public function updateSubmission(Request $request, $id)
    {
        $submission = FormSubmission::with('form.fields')->findOrFail($id);
        $form = $submission->form;

        $rules = [];
        $attributes = [];

        foreach ($form->fields as $field) {
            $key = "fields.{$field->id}";
            $attributes[$key] = $field->label;

            if ($field->type === 'date') {
                $rules[$key] = ['required', 'date_format:d/m/Y'];
                continue;
            }

            if ($field->type === 'time') {
                $rules[$key] = [
                    'required',
                    'regex:/^(0?[1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)$/i'
                ];
                continue;
            }

            if ($field->is_required) {
                $rules[$key] = 'required';
            }
        }

        $request->validate($rules, ['required' => ':attribute is required.'], $attributes);

        foreach ($request->input('fields', []) as $fieldId => $value) {
            $field = $form->fields->firstWhere('id', $fieldId);
            if (!$field) continue;

            if ($field->type === 'date') {
                $value = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            if ($field->type === 'time') {
                $value = Carbon::createFromFormat('h:i A', strtoupper($value))
                    ->format('H:i:s');
            }

            FormSubmissionValue::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'field_id' => $fieldId,
                ],
                [
                    'value' => $value
                ]
            );
        }

        return redirect()
            ->route('forms.submissions', $form->id)
            ->with('success', 'Submission updated successfully!');
    }


    public function deleteSubmission($id)
    {
        FormSubmission::findOrFail($id)->delete();
        return back()->with('success', 'Submission deleted');
    }

    public function showSubmission($submissionId)
    {
        $submission = FormSubmission::with(['values.field', 'form.fields'])
            ->findOrFail($submissionId);

        $form = $submission->form;
        $forms = $this->sidebarForms();

        $values = $submission->values->pluck('value', 'field_id')->toArray();

        return view('submissions.show', compact(
            'submission',
            'form',
            'values',
            'forms'
        ));
    }

    public function submissionsDatatable($formId)
    {
        $form = Forms::with('fields')->findOrFail($formId);

        $query = FormSubmission::with('values')
            ->where('form_id', $formId);

        return DataTables::of($query)
            ->addColumn('submitted_on', function ($submission) {
                return '
                ' . $submission->created_at->format('d M Y') . '
                <div class="text-muted small">
                    ' . $submission->created_at->format('h:i A') . '
                </div>
            ';
            })

            ->addColumn('fields', function ($submission) use ($form) {
                $row = [];
                $searchableText = []; // store searchable values

                foreach ($form->fields as $field) {
                    $record = $submission->values->firstWhere('field_id', $field->id);
                    $value = '-';

                    if ($record && $record->value) {
                        if ($field->type === 'date') {
                            $value = Carbon::parse($record->value)->format('d/m/Y');
                        } elseif ($field->type === 'time') {
                            $value = Carbon::parse($record->value)->format('h:i A');
                        } else {
                            $value = $record->value;
                        }
                    }

                    $row[] = $value;
                    $searchableText[] = strip_tags($value); // collect for search
                }

                // add a hidden property for searching
                $row['searchable_text'] = implode(' ', $searchableText);

                return $row;
            })

            ->addColumn('action', function ($submission) {
                $view = route('submissions.show', $submission->id);
                $edit = route('submissions.edit', $submission->id);
                $delete = route('submissions.destroy', $submission->id);

                return '
            <div class="dropdown">
                <button class="btn btn-link text-primary p-0 border-0"
                        data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots fs-5"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm position-fixed">
                    <li><a class="dropdown-item text-primary" href="' . $view . '"><i class="bi bi-eye me-2"></i> View</a></li>
                    <li><a class="dropdown-item text-primary" href="' . $edit . '"><i class="bi bi-pencil me-2"></i> Edit</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="' . $delete . '">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button class="dropdown-item text-danger delete-btn"><i class="bi bi-trash me-2"></i> Delete</button>
                        </form>
                    </li>
                </ul>
            </div>';
            })

            ->filter(function ($query) use ($form) {
                if ($search = request()->get('search')['value']) {
                    $query->where(function ($q) use ($search, $form) {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhere('created_at', 'like', "%{$search}%")
                            ->orWhereHas('values', function ($q2) use ($search) {
                                $q2->where('value', 'like', "%{$search}%");
                            });
                    });
                }
            })

            ->rawColumns(['submitted_on', 'action'])
            ->make(true);
    }
}
