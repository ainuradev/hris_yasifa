<?php

namespace App\Livewire\Admin\Karyawan;

use App\Models\Employee;
use App\Models\Unit;
use App\Services\EmployeeImportService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class Import extends Component
{
    use WithFileUploads;

    public $step = 1;
    public $file;
    public $headers = [];
    public $previewData = [];
    public $mapping = [];
    
    // Default values
    public $defaultUnitId = null;
    public $availableUnits = [];

    // Results
    public $importResults = [
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];

    public $dbFields = [
        'name' => 'Nama Lengkap (*)',
        'nik' => 'NIK (*)',
        'nuptk' => 'NUPTK',
        'npk' => 'NPK Kemenag',
        'email' => 'Email (*)',
        'phone' => 'No. Handphone',
        'address' => 'Alamat Lengkap',
        'place_of_birth' => 'Tempat Lahir',
        'date_of_birth' => 'Tanggal Lahir',
        'gender' => 'Jenis Kelamin (L/P)',
        'agama' => 'Agama',
        'nama_ibu_kandung' => 'Nama Ibu Kandung',
        'status_perkawinan' => 'Status Perkawinan',
        'pendidikan_terakhir' => 'Pendidikan Terakhir',
        'tahun_lulus' => 'Tahun Lulus',
        'status_kepegawaian' => 'Status Kepegawaian',
        'tmt_pegawai' => 'TMT Pegawai',
        'no_sk_pengangkatan' => 'No SK Pengangkatan'
    ];

    public function mount()
    {
        if (auth()->user()->isAdminUnit()) {
            $this->defaultUnitId = auth()->user()->unit_id;
        } else {
            $this->availableUnits = Unit::orderBy('name')->get();
        }
    }

    public function processUpload()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:10240', // 10MB max
        ]);

        try {
            $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array) { return $array; }
            }, $this->file->getRealPath());

            if (empty($data) || empty($data[0])) {
                session()->flash('error', 'File excel kosong atau format tidak valid.');
                return;
            }

            $sheet = $data[0];
            // Assuming first row is headers
            $this->headers = array_filter($sheet[0]);
            
            // Preview next 3 rows
            $this->previewData = array_slice($sheet, 1, 3);
            
            // Auto-map based on similar names
            $this->autoMapColumns();

            $this->step = 2;
        } catch (Throwable $e) {
            try {
                Log::error('Gagal membaca file import karyawan.', [
                    'user_id' => auth()->id(),
                    'file_name' => $this->file?->getClientOriginalName(),
                    'exception' => $e,
                ]);
            } catch (Throwable) {
                // Keep the flash error available even when storage/logs is not writable.
            }

            session()->flash('error', 'Gagal membaca file import. Pastikan format file valid dan folder storage writable.');
        }
    }

    private function autoMapColumns()
    {
        foreach ($this->dbFields as $fieldKey => $fieldLabel) {
            foreach ($this->headers as $index => $header) {
                if (stripos($header, $fieldKey) !== false || stripos($fieldLabel, $header) !== false) {
                    $this->mapping[$fieldKey] = $index;
                    break;
                }
            }
        }
    }

    public function executeImport(EmployeeImportService $importService)
    {
        // Validation for required mapping
        if (!isset($this->mapping['name']) || !isset($this->mapping['nik']) || !isset($this->mapping['email'])) {
            session()->flash('error', 'Kolom Nama, NIK, dan Email wajib dipetakan (diisi).');
            return;
        }

        try {
            $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array) { return $array; }
            }, $this->file->getRealPath());

            $sheet = $data[0];
            $rows = array_slice($sheet, 1); // Skip header

            foreach ($rows as $rowIndex => $row) {
                // Skip completely empty rows
                if (empty(array_filter($row))) continue;

                $cleanData = $importService->cleanRow($row, $this->mapping);
                
                // Determine Unit ID
                // Logic: Priority goes to manual defaultUnitId. If not set, try to find in excel (if mapped).
                $unitId = $this->defaultUnitId;
                
                // SECURITY: Enforce Admin Unit to ONLY import to their own unit
                if (auth()->user()->isAdminUnit()) {
                    $unitId = auth()->user()->unit_id;
                }

                if (!$unitId && isset($this->mapping['unit_name'])) {
                    // Advanced: Find unit by name
                    // For now, if Admin Pusat doesn't select, we might skip or set null if allowed
                }

                // Required fields check after clean
                $missingFields = [];
                if (empty($cleanData['name'])) $missingFields[] = 'Nama';
                if (empty($cleanData['nik'])) $missingFields[] = 'NIK';
                if (empty($cleanData['email'])) $missingFields[] = 'Email';
                
                if (!empty($missingFields)) {
                    $this->importResults['failed']++;
                    $this->importResults['errors'][] = "Baris " . ($rowIndex + 2) . ": " . implode(', ', $missingFields) . " kosong.";
                    continue;
                }

                // Check duplicate NIK or Email
                if (Employee::where('nik', $cleanData['nik'])->orWhere('email', $cleanData['email'])->exists()) {
                    $this->importResults['failed']++;
                    $this->importResults['errors'][] = "Baris " . ($rowIndex + 2) . ": NIK ({$cleanData['nik']}) atau Email ({$cleanData['email']}) sudah terdaftar.";
                    continue;
                }

                $cleanData['unit_id'] = $unitId;
                $cleanData['password'] = Hash::make($importService->generatePassword($cleanData['date_of_birth'] ?? null));
                $cleanData['must_change_password'] = true;
                
                // Set default type if missing
                if (!isset($cleanData['type'])) {
                    $cleanData['type'] = 'guru';
                }

                // Set default role and status
                $cleanData['role'] = 'karyawan';
                $cleanData['status'] = 'aktif';

                Employee::create($cleanData);
                $this->importResults['success']++;
            }

            $this->step = 3;
        } catch (Throwable $e) {
            try {
                Log::error('Gagal menjalankan import karyawan.', [
                    'user_id' => auth()->id(),
                    'file_name' => $this->file?->getClientOriginalName(),
                    'mapping' => $this->mapping,
                    'default_unit_id' => $this->defaultUnitId,
                    'exception' => $e,
                ]);
            } catch (Throwable) {
                // Keep the flash error available even when storage/logs is not writable.
            }

            session()->flash('error', 'Import karyawan gagal. Pastikan data sesuai template dan folder storage writable.');
        }
    }

    public function render()
    {
        return view('components.admin.karyawan.import')->layout('components.layouts.app');
    }
}
