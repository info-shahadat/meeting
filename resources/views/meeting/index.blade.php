@extends('layouts.master')
@section('title', 'Softvence :: Meetings')
@section('page-title', 'Meetings')

@section('content')

<div class="py-4">
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
        <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
            <li class="breadcrumb-item">
                <a href="#">
                    <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                </a>
            </li>
            <li class="breadcrumb-item"><a href="#">Softvence</a></li>
            <li class="breadcrumb-item active" aria-current="page">Meetings</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow components-section">
            <div class="card-body">

                <h4 class="mb-4">Create a New Meeting</h4>

                <form method="POST" action="{{ route('meet.create') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Meeting Title</label>
                        <input type="text"
                               name="title"
                               class="form-control"
                               placeholder="Team Sync Meeting"
                               required>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            Create Meeting
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

@endsection

{{-- @section('script')
@endsection --}}
