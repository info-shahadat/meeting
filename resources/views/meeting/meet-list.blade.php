@extends('layouts.master')
@section('title', 'Softvence :: Meeting List')
@section('page-title', 'Meeting List')

@section('content')

    <div class="py-4 d-flex justify-content-between align-items-center" style="padding-right: 23px;">
        <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
                <li class="breadcrumb-item">
                    <a href="#">
                        <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </a>
                </li>
                <li class="breadcrumb-item"><a href="#">Softvence</a></li>
                <li class="breadcrumb-item active" aria-current="page">Meeting List</li>
            </ol>
        </nav>

        {{-- <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
            + Create User
        </a> --}}
    </div>

    <div class="card border-0 shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table id="meetTable" class="table table-hover table-bordered nowrap" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Room ID</th>
                            <th>Creator Name</th>
                            <th>Creation Time</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Meeting Modal -->
    <div class="modal fade" id="editMeetingModal" tabindex="-1" aria-labelledby="editMeetingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <form id="editMeetingForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header">
            <h5 class="modal-title" id="editMeetingModalLabel">Edit Meeting</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <div class="mb-3">
                <label for="editTitle" class="form-label">Meeting Title</label>
                <input type="text" id="editTitle" name="title" class="form-control" required>
            </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Meeting</button>
            </div>
        </form>
        </div>
    </div>
    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#meetTable').DataTable({
                ajax: {
                    url: '{{ route('meet.list.data') }}',
                    dataSrc: 'data'
                },
                columns: [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: 'title' },
                    { data: 'room' },
                    { data: 'creator' },
                    { data: 'created_at' },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return `
                                <a href="/meet/${row.room}" class="btn btn-sm btn-primary me-1">Join</a>
                                <button class="btn btn-sm btn-warning me-1 editBtn" data-room="${row.room}" data-title="${row.title}">Edit</button>
                                <form action="/meet-delete/${row.room}" method="POST" class="d-inline deleteForm">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>`;
                        },
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                responsive: true,
                order: [[4, 'desc']]
            });
        });

        $(document).on('submit', '.deleteForm', function(e) {
            e.preventDefault();
            const form = this;

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary me-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
        
        $(document).on('click', '.editBtn', function() {
            const room = $(this).data('room');
            const title = $(this).data('title');

            $('#editTitle').val(title);
            $('#editMeetingForm').attr('action', `/meet-update/${room}`);

            // Show modal
            var editModal = new bootstrap.Modal(document.getElementById('editMeetingModal'));
            editModal.show();
        });

        // Handle modal form submission
        $('#editMeetingForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const title = $('#editTitle').val();

            $.ajax({
                url: url,
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    title: title
                },
                success: function(response) {
                    $('#editMeetingModal').modal('hide');
                    $('#meetTable').DataTable().ajax.reload(null, false); // Reload table without resetting pagination
                    const notyf = new Notyf({ position: { x: 'right', y: 'top' }, duration: 3000, dismissible: true });
                    notyf.success('Meeting updated successfully');
                },
                error: function(err) {
                    alert('Failed to update meeting');
                }
            });
        });

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notyf = new Notyf({
                position: {
                    x: 'right',
                    y: 'top',
                },
                duration: 3000,
                dismissible: true
            });

            @if (session('success'))
                notyf.success("{{ session('success') }}");
            @endif

            @if (session('error'))
                notyf.error("{{ session('error') }}");
            @endif
        });
    </script>
@endsection
