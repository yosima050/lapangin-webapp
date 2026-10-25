@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">Pengaturan Harga Khusus</h1>
            <p class="text-muted mb-0">
                Atur tarif khusus berdasarkan tanggal dan rentang jam tanpa mengubah harga master.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Periksa kembali data berikut:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Tambah Harga Khusus</h2>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.harga-khusus.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <label for="lapangan_id" class="form-label">Lapangan</label>
                        <select name="lapangan_id" id="lapangan_id" class="form-select" required>
                            <option value="">Pilih lapangan</option>
                            @foreach ($lapangans as $lapangan)
                                <option
                                    value="{{ $lapangan->id }}"
                                    @selected(old('lapangan_id') == $lapangan->id)
                                >
                                    {{ $lapangan->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input
                            type="date"
                            name="tanggal"
                            id="tanggal"
                            class="form-control"
                            value="{{ old('tanggal') }}"
                            required
                        >
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <label for="harga" class="form-label">Tarif (Rp)</label>
                        <input
                            type="number"
                            name="harga"
                            id="harga"
                            class="form-control"
                            min="0"
                            max="9999999999.99"
                            step="0.01"
                            value="{{ old('harga') }}"
                            placeholder="Contoh: 175000"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="jam_mulai" class="form-label">Jam Mulai</label>
                        <input
                            type="time"
                            name="jam_mulai"
                            id="jam_mulai"
                            class="form-control"
                            value="{{ old('jam_mulai') }}"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="jam_selesai" class="form-label">Jam Selesai</label>
                        <input
                            type="time"
                            name="jam_selesai"
                            id="jam_selesai"
                            class="form-control"
                            value="{{ old('jam_selesai') }}"
                            required
                        >
                    </div>

                    <div class="col-12">
                        <label for="keterangan" class="form-label">Keterangan (opsional)</label>
                        <input
                            type="text"
                            name="keterangan"
                            id="keterangan"
                            class="form-control"
                            maxlength="150"
                            value="{{ old('keterangan') }}"
                            placeholder="Contoh: Hari libur nasional atau jam ramai"
                        >
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        Simpan Harga Khusus
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Daftar Aturan Harga</h2>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Lapangan</th>
                            <th>Tanggal</th>
                            <th>Rentang Jam</th>
                            <th>Tarif</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hargaKhusus as $item)
                            <tr>
                                <td>{{ $item->lapangan?->nama ?? 'Lapangan tidak tersedia' }}</td>
                                <td>{{ $item->tanggal->format('d-m-Y') }}</td>
                                <td>
                                    {{ substr($item->jam_mulai, 0, 5) }}
                                    – {{ substr($item->jam_selesai, 0, 5) }}
                                </td>
                                <td>Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</td>
                                <td>{{ $item->keterangan ?: '—' }}</td>
                                <td>
                                    @if ($item->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form
                                        action="{{ route('admin.harga-khusus.toggle-status', $item->id) }}"
                                        method="POST"
                                        class="d-inline"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="btn btn-sm {{ $item->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                        >
                                            {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <form
                                        action="{{ route('admin.harga-khusus.destroy', $item->id) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus aturan harga ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Belum ada aturan harga khusus.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection