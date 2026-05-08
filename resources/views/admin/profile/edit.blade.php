<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @php
        $photoUrl = $employee->photo_path ? asset('storage/' . $employee->photo_path) : null;
        $nameParts = preg_split('/\s+/', trim($employee->name));
        $initials = strtoupper(substr($nameParts[0] ?? $employee->name, 0, 1) . substr($nameParts[1] ?? $employee->name, 0, 1));
        $roleValue = $employee->role->value ?? $employee->role;
        $statusValue = $employee->status->value ?? $employee->status;
        $jobTitle = $employee->teacherDetail?->jabatan ?? $employee->nonTeacherDetail?->jabatan ?? '-';
    @endphp

    <style>
        :root {
            --primary-teal: #0D9488;
            --primary-teal-dark: #134E4A;
            --primary-light: #F0FDF4;
            --surface: rgba(255, 255, 255, 0.82);
            --surface-strong: rgba(255, 255, 255, 0.94);
            --border: rgba(13, 148, 136, 0.12);
            --text-main: #134E4A;
            --text-soft: #5F7C78;
            --danger-soft: #FEF2F2;
            --danger-text: #B91C1C;
            --warning-soft: #FFFBEB;
            --warning-text: #B45309;
            --shadow-soft: 0 24px 50px rgba(13, 148, 136, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            background:
                radial-gradient(circle at top left, rgba(13, 148, 136, 0.14), transparent 24rem),
                radial-gradient(circle at bottom right, rgba(13, 148, 136, 0.12), transparent 26rem),
                var(--primary-light);
        }

        .page {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0 40px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--primary-teal), var(--primary-teal-dark));
            color: white;
            font-weight: 800;
            box-shadow: 0 14px 28px rgba(13, 148, 136, 0.22);
        }

        .brand-copy small {
            display: block;
            font-size: 12px;
            color: var(--primary-teal);
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .brand-copy strong {
            display: block;
            margin-top: 4px;
            font-size: 20px;
        }

        .brand-copy span {
            display: block;
            margin-top: 4px;
            color: var(--text-soft);
            font-size: 14px;
        }

        .topbar-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 14px;
            border: 1px solid transparent;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.76);
            border-color: var(--border);
            color: var(--primary-teal-dark);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-teal), #0B7A6F);
            color: white;
            box-shadow: 0 14px 28px rgba(13, 148, 136, 0.2);
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .hero,
        .panel {
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: 28px;
            box-shadow: var(--shadow-soft);
        }

        .hero {
            padding: 28px;
            margin-bottom: 22px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(260px, 0.8fr);
            gap: 22px;
            align-items: center;
        }

        .hero-main {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .avatar {
            width: 96px;
            height: 96px;
            border-radius: 28px;
            overflow: hidden;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary-teal), var(--primary-teal-dark));
            color: white;
            display: grid;
            place-items: center;
            font-size: 34px;
            font-weight: 800;
            box-shadow: 0 16px 30px rgba(13, 148, 136, 0.2);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .eyebrow {
            margin: 0 0 10px;
            font-size: 12px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--primary-teal);
            font-weight: 800;
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(30px, 4vw, 42px);
            line-height: 1.05;
        }

        .hero p {
            margin: 10px 0 0;
            max-width: 640px;
            line-height: 1.7;
            color: var(--text-soft);
        }

        .pill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .pill {
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .pill-teal {
            background: #DFF7F2;
            color: var(--primary-teal-dark);
        }

        .pill-green {
            background: #DCFCE7;
            color: #166534;
        }

        .pill-white {
            background: white;
            color: #4B5563;
            border: 1px solid #E5E7EB;
        }

        .hero-side {
            display: grid;
            gap: 12px;
        }

        .meta-card {
            background: var(--surface-strong);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 16px 18px;
        }

        .meta-card small {
            display: block;
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--primary-teal);
            font-weight: 800;
        }

        .meta-card strong {
            display: block;
            margin-top: 8px;
            font-size: 15px;
        }

        .flash,
        .warning {
            border-radius: 22px;
            padding: 16px 18px;
            margin-bottom: 18px;
            font-size: 14px;
            line-height: 1.7;
        }

        .flash-success {
            background: #ECFDF5;
            color: #166534;
            border: 1px solid #BBF7D0;
        }

        .flash-error {
            background: var(--danger-soft);
            color: var(--danger-text);
            border: 1px solid #FECACA;
        }

        .warning {
            background: var(--warning-soft);
            color: var(--warning-text);
            border: 1px solid #FDE68A;
        }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.8fr);
            gap: 22px;
        }

        .stack {
            display: grid;
            gap: 22px;
        }

        .panel {
            padding: 24px;
        }

        .panel h2 {
            margin: 0;
            font-size: 22px;
        }

        .panel p.lead {
            margin: 8px 0 0;
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.7;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 22px;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-teal-dark);
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid rgba(13, 148, 136, 0.18);
            border-radius: 16px;
            background: rgba(240, 253, 244, 0.7);
            padding: 14px 16px;
            font: inherit;
            color: var(--text-main);
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary-teal);
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.12);
            background: white;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .hint {
            margin-top: 8px;
            color: var(--text-soft);
            font-size: 12px;
            line-height: 1.6;
        }

        .error {
            margin-top: 8px;
            color: #DC2626;
            font-size: 12px;
        }

        .upload-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px;
            border-radius: 20px;
            border: 1px dashed rgba(13, 148, 136, 0.28);
            background: linear-gradient(135deg, rgba(240, 253, 244, 0.95), rgba(255, 255, 255, 0.95));
        }

        .upload-meta strong {
            display: block;
            font-size: 14px;
        }

        .upload-meta span {
            display: block;
            margin-top: 6px;
            color: var(--text-soft);
            font-size: 13px;
        }

        .upload-button {
            position: relative;
            overflow: hidden;
            min-width: 140px;
        }

        .upload-button input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .info-list {
            display: grid;
            gap: 14px;
            margin-top: 22px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(13, 148, 136, 0.1);
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-item span {
            color: var(--text-soft);
            font-size: 14px;
        }

        .info-item strong {
            text-align: right;
            max-width: 220px;
            font-size: 14px;
        }

        .footer-action {
            position: sticky;
            bottom: 14px;
            margin-top: 22px;
            display: flex;
            justify-content: flex-end;
        }

        .footer-card {
            width: min(100%, 420px);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 22px;
            box-shadow: var(--shadow-soft);
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .footer-card p {
            margin: 0;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 980px) {
            .hero-grid,
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .page {
                width: min(100% - 20px, 100%);
                padding-top: 18px;
            }

            .topbar,
            .hero-main,
            .upload-box,
            .footer-card {
                flex-direction: column;
                align-items: stretch;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .hero,
            .panel {
                padding: 20px;
            }

            .avatar {
                width: 84px;
                height: 84px;
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <div class="brand">
                <div class="brand-mark">AP</div>
                <div class="brand-copy">
                    <small>Admin Profile</small>
                    <strong>HRIS Sirojul Falah</strong>
                    <span>Halaman profil admin dengan tampilan yang konsisten dengan halaman login.</span>
                </div>
            </div>

            <div class="topbar-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Kembali ke Panel</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Logout</button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif

        @if ($employee->must_change_password)
            <div class="warning">
                Password bawaan masih aktif. Demi keamanan, Anda wajib mengganti password terlebih dahulu sebelum memakai menu admin lainnya.
            </div>
        @endif

        <section class="hero">
            <div class="hero-grid">
                <div class="hero-main">
                    <div class="avatar">
                        @if ($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Foto profil">
                        @else
                            {{ $initials }}
                        @endif
                    </div>

                    <div>
                        <p class="eyebrow">Profil Saya</p>
                        <h1>{{ $employee->name }}</h1>
                        <p>Kelola identitas akun, data pribadi, keamanan akses, dan informasi kepegawaian dari satu halaman yang lebih rapi dan fokus.</p>

                        <div class="pill-row">
                            <span class="pill pill-teal">{{ str($roleValue)->replace('_', ' ')->title() }}</span>
                            <span class="pill pill-green">{{ str($statusValue)->replace('_', ' ')->title() }}</span>
                            <span class="pill pill-white">{{ $jobTitle }}</span>
                        </div>
                    </div>
                </div>

                <div class="hero-side">
                    <div class="meta-card">
                        <small>Unit</small>
                        <strong>{{ $employee->unit?->name ?? '-' }}</strong>
                    </div>
                    <div class="meta-card">
                        <small>NIK</small>
                        <strong>{{ $employee->nik }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="content-grid">
                <div class="stack">
                    <section class="panel">
                        <h2>Identitas Utama</h2>
                        <p class="lead">Pastikan nama dan email Anda selalu aktif agar akun admin tetap mudah diverifikasi.</p>

                        <div class="form-grid">
                            <div>
                                <label for="name">Nama lengkap</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $employee->name) }}">
                                @error('name') <div class="error">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label for="email">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $employee->email) }}">
                                @error('email') <div class="error">{{ $message }}</div> @enderror
                            </div>

                            <div class="field-full">
                                <label for="photo">Foto profil</label>
                                <div class="upload-box">
                                    <div class="upload-meta">
                                        <strong>Unggah foto baru</strong>
                                        <span id="photo-name">PNG/JPG maksimal 2 MB</span>
                                    </div>
                                    <label class="btn btn-primary upload-button">
                                        Pilih file
                                        <input id="photo" type="file" name="photo" accept="image/*">
                                    </label>
                                </div>
                                @error('photo') <div class="error">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <h2>Data Pribadi</h2>
                        <p class="lead">Lengkapi biodata dasar agar data administrasi dan kontak internal tetap sinkron.</p>

                        <div class="form-grid">
                            <div>
                                <label for="phone">No. HP</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone', $employee->phone) }}">
                                @error('phone') <div class="error">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label for="place_of_birth">Tempat lahir</label>
                                <input id="place_of_birth" type="text" name="place_of_birth" value="{{ old('place_of_birth', $employee->place_of_birth) }}">
                                @error('place_of_birth') <div class="error">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label for="date_of_birth">Tanggal lahir</label>
                                <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($employee->date_of_birth)->format('Y-m-d')) }}">
                                @error('date_of_birth') <div class="error">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label for="gender">Jenis kelamin</label>
                                <select id="gender" name="gender">
                                    <option value="">Pilih</option>
                                    <option value="laki_laki" @selected(old('gender', $employee->gender) === 'laki_laki')>Laki-laki</option>
                                    <option value="perempuan" @selected(old('gender', $employee->gender) === 'perempuan')>Perempuan</option>
                                </select>
                                @error('gender') <div class="error">{{ $message }}</div> @enderror
                            </div>

                            <div class="field-full">
                                <label for="address">Alamat</label>
                                <textarea id="address" name="address">{{ old('address', $employee->address) }}</textarea>
                                @error('address') <div class="error">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <div class="stack">
                    <section class="panel">
                        <h2>Kontak Darurat</h2>
                        <p class="lead">Simpan kontak cadangan yang bisa dihubungi jika ada keperluan mendesak.</p>

                        <div class="form-grid" style="grid-template-columns: 1fr;">
                            <div>
                                <label for="emergency_contact_name">Nama kontak darurat</label>
                                <input id="emergency_contact_name" type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                                @error('emergency_contact_name') <div class="error">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label for="emergency_contact_phone">No. HP kontak darurat</label>
                                <input id="emergency_contact_phone" type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
                                @error('emergency_contact_phone') <div class="error">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <h2>Keamanan Akun</h2>
                        <p class="lead">Ganti password untuk menjaga akses panel admin tetap aman.</p>

                        <div class="form-grid" style="grid-template-columns: 1fr;">
                            <div>
                                <label for="current_password">Password saat ini</label>
                                <input id="current_password" type="password" name="current_password">
                                <div class="hint">
                                    {{ $employee->must_change_password ? 'Masukkan password bawaan untuk verifikasi sebelum mengganti password.' : 'Kosongkan bila Anda tidak ingin mengganti password.' }}
                                </div>
                                @error('current_password') <div class="error">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label for="password">Password baru</label>
                                <input id="password" type="password" name="password">
                                @error('password') <div class="error">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label for="password_confirmation">Konfirmasi password baru</label>
                                <input id="password_confirmation" type="password" name="password_confirmation">
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <h2>Informasi Kepegawaian</h2>
                        <p class="lead">Ringkasan data status kerja yang saat ini tersimpan di sistem.</p>

                        <div class="info-list">
                            <div class="info-item">
                                <span>Jabatan</span>
                                <strong>{{ $jobTitle }}</strong>
                            </div>
                            <div class="info-item">
                                <span>Role</span>
                                <strong>{{ str($roleValue)->replace('_', ' ')->title() }}</strong>
                            </div>
                            <div class="info-item">
                                <span>Status</span>
                                <strong>{{ str($statusValue)->replace('_', ' ')->title() }}</strong>
                            </div>
                            <div class="info-item">
                                <span>Kontrak berakhir</span>
                                <strong>{{ $employee->contract_end_date?->format('d M Y') ?? '-' }}</strong>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="footer-action">
                <div class="footer-card">
                    <p>Perubahan yang disimpan di sini langsung memperbarui data akun admin Anda.</p>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const photoInput = document.getElementById('photo');
        const photoName = document.getElementById('photo-name');

        if (photoInput && photoName) {
            photoInput.addEventListener('change', function () {
                photoName.textContent = this.files[0] ? this.files[0].name : 'PNG/JPG maksimal 2 MB';
            });
        }
    </script>
</body>
</html>
