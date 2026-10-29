@extends('layouts.auth')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Deployment Lab</h1>
            <p class="text-muted mb-0">Identitas rilis dan data uji deployment.</p>
        </div>
        <span class="badge fs-6 {{ $release['environment'] === 'production' ? 'bg-danger' : ($release['environment'] === 'qa' ? 'bg-warning text-dark' : 'bg-primary') }}">{{ strtoupper($release['environment']) }}</span>
    </div>
    <div class="alert alert-info d-flex flex-wrap align-items-center gap-3" role="status">
        <span class="badge bg-primary">Baru · {{ $release['version'] }}</span>
        <div>
            <strong>Pembaruan aplikasi</strong>
            <div>Ringkasan dan catatan Anda kini tampil dalam satu dashboard.</div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Rilis {{ $release['version'] }}</div>
        <div class="card-body">
            <p>{{ $release['message'] }}</p>
            <dl class="row mb-0">
                @foreach (['branch' => 'Branch', 'commit' => 'Commit', 'build' => 'Build', 'branch_target' => 'Target branch'] as $key => $label)
                    <dt class="col-sm-3">{{ $label }}</dt><dd class="col-sm-9 text-break">{{ $release[$key] }}</dd>
                @endforeach
            </dl>
            @if ($release['branch_target'] !== 'local' && $release['branch_target'] !== $release['environment'])
                <div class="alert alert-warning mt-3 mb-0">Target branch berbeda dengan environment aktif. Periksa tujuan deployment.</div>
            @endif
            <div class="mt-3"><a href="/health">Health</a> · <a href="/ready">Readiness</a> · <a href="/version">Version JSON</a></div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5">Ringkasan catatan</h2>
            <p class="fs-4 mb-1"><strong>{{ $notes->total() }}</strong> catatan tersimpan</p>
            <p class="text-muted mb-0">Total seluruh catatan Anda, termasuk yang ada di halaman berikutnya.</p>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Catatan saya</div>
        <div class="card-body">
            <p class="text-muted">Simpan catatan sebelum deployment, lalu pastikan catatan tetap ada setelah rilis baru atau rollback.</p>
            @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('deployment.notes.store') }}" class="d-flex gap-2 mb-4">
                @csrf
                <input name="body" class="form-control" aria-label="Catatan deployment" placeholder="Contoh: data sebelum rilis 1.1.0" maxlength="255" value="{{ old('body') }}" required>
                <button class="btn btn-primary" type="submit">Simpan</button>
            </form>
            @forelse ($notes as $note)
                <div class="d-flex justify-content-between gap-3 border-top py-3">
                    <div><div class="text-break">{{ $note->body }}</div><small class="text-muted">#{{ $note->id }} · {{ $note->created_at }}</small></div>
                    <form method="POST" action="{{ route('deployment.notes.destroy', $note) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </div>
            @empty
                <p>Belum ada catatan.</p>
            @endforelse
            {{ $notes->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
