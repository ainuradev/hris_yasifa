<?php

namespace App\Livewire\Admin;

use App\Enums\EmployeeType;
use App\Models\Employee;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeList extends Component
{
    use WithPagination;

    public $search = '';
    public $unit_id = '';
    public $type = '';
    public $status = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'unit_id' => ['except' => ''],
        'type' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingUnitId()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $admin = auth()->user();

        $employees = Employee::with(['unit', 'teacherDetail.salaryRate', 'nonTeacherDetail.salaryRate'])
            ->visibleToAdmin($admin)
            ->when(!empty($this->search), function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nik', 'like', "%{$this->search}%");
                });
            })
            ->when(!empty($this->unit_id), fn ($query) => $query->where('unit_id', $this->unit_id))
            ->when(!empty($this->type), fn ($query) => $query->where('type', $this->type))
            ->when(!empty($this->status), fn ($query) => $query->where('status', $this->status))
            ->orderBy('name')
            ->paginate(20);

        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();

        return view('livewire.admin.employee-list', [
            'employees' => $employees,
            'units' => $units,
        ]);
    }
}
