@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<h1 style="margin-bottom: 2rem;">Audit Logs</h1>

<div class="card">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="background: var(--light); text-align: left;">
                    <th style="padding: 0.7rem;">Time</th>
                    <th style="padding: 0.7rem;">User</th>
                    <th style="padding: 0.7rem;">Action</th>
                    <th style="padding: 0.7rem;">Description</th>
                    <th style="padding: 0.7rem;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 0.7rem;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td style="padding: 0.7rem;">{{ $log->user ? $log->user->name : 'System' }}</td>
                        <td style="padding: 0.7rem;">
                            <span style="padding: 0.2rem 0.5rem; background: var(--light); border-radius: 4px; font-family: monospace;">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td style="padding: 0.7rem;">{{ $log->description }}</td>
                        <td style="padding: 0.7rem; font-family: monospace; font-size: 0.8rem;">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1rem;">
    {{ $logs->links() }}
</div>

<div style="margin-top: 1rem;">
    <a href="/api/v1/audit-logs/export" class="btn btn-secondary">📥 Export to CSV</a>
</div>
@endsection
