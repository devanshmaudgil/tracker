@extends('layouts.app')

@section('title', 'Months')

@section('content')
<div class="content-header">
    <h1>Months</h1>
    <a href="{{ route('months.create') }}" class="btn btn-primary">Add Month</a>
</div>

<style>
    .table-container table {
        font-size: 12px;
    }
    .table-container th,
    .table-container td {
        text-align: center;
        padding: 6px 8px;
    }
    .table-container th {
        font-size: 11px;
        white-space: nowrap;
    }
</style>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Month</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($months as $month)
                <tr>
                    <td>{{ $month->id }}</td>
                    <td>{{ $month->month }}</td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('months.edit', $month->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('months.destroy', $month->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this month?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 30px; color: #666;">No months found. <a href="{{ route('months.create') }}" style="color: #f1cd86;">Add your first month</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

