@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Hotels</h2>
        <button class="btn btn-primary mb-3" id="createHotelBtn">+ Add Hotel</button>
        <button class="btn btn-warning mb-3" id="bulkEditBtn" disabled>Bulk Edit</button>
        <button class="btn btn-danger mb-3" id="bulkDeleteBtn" disabled>Bulk Delete</button>


        <table class="table table-bordered" id="hotelsTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>ID</th>
                    <th>Display Name</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Star Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div id="pagination" class="mt-3"></div>
    </div>

    <!-- Hotel Form Modal -->
    <div class="modal fade" id="hotelModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="hotelForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="hotel_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hotel Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row">
                        <div id="formErrors" class="alert alert-danger d-none"></div>

                        @php
                            $fields = [
                                ['display_name', 'Display Name', 'text'],
                                ['name', 'Name', 'text'],
                                ['country_code', 'Country Code', 'text'],
                                ['country_name', 'Country Name', 'text'],
                                ['state', 'State', 'text'],
                                ['city_name', 'City Name', 'text'],
                                ['address', 'Address', 'text'],
                                ['description', 'Description', 'textarea'],
                                ['zip_code', 'Zip Code', 'text'],
                                ['star_rating', 'Star Rating', 'number'],
                                ['room_count', 'Room Count', 'number'],
                                ['lat', 'Latitude', 'text'],
                                ['lng', 'Longitude', 'text'],
                                ['phone', 'Phone', 'text'],
                                ['fax', 'Fax', 'text'],
                                ['email', 'Email', 'email'],
                                ['website', 'Website', 'text'],
                                ['property_category', 'Property Category', 'text'],
                                ['property_sub_category', 'Property Sub Category', 'text'],
                                ['chain_code', 'Chain Code', 'text'],
                                ['facilities', 'Facilities', 'textarea'],
                                ['priority', 'Priority', 'number']
                            ];
                        @endphp

                        @foreach ($fields as [$id, $label, $type])
                            <div class="col-md-6 mb-2">
                                <label for="{{ $id }}">{{ $label }}</label>
                                @if ($type === 'textarea')
                                    <textarea name="{{ $id }}" id="{{ $id }}" class="form-control"></textarea>
                                @else
                                    <input type="{{ $type }}" name="{{ $id }}" id="{{ $id }}" class="form-control">
                                @endif
                                <small class="text-danger error-{{ $id }}"></small>
                            </div>
                        @endforeach

                        <div class="col-md-6 mb-2">
                            <label for="images">Images</label>
                            <input type="file" name="images[]" id="images" class="form-control" multiple>
                            <small class="text-danger error-images"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const modal = new bootstrap.Modal(document.getElementById('hotelModal'));
        const tableBody = $('#hotelsTable tbody');
        const pagination = $('#pagination');

        function loadHotels(page = 1) {
            $.get(`/hotels?page=${page}`, function (res) {
                tableBody.empty();
                res.data.forEach(h => {
                    tableBody.append(`
                                            <tr>
                                                <td><input type="checkbox" class="hotelCheckbox" value="${h.id}"></td>
                                                <td>${h.id}</td>
                                                <td>${h.display_name}</td>
                                                <td>${h.city_name || ''}</td>
                                                <td>${h.country_name || ''}</td>
                                                <td>${h.star_rating || ''}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info editBtn" data-id="${h.id}">Edit</button>
                                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${h.id}">Delete</button>
                                                </td>
                                            </tr>
                                        `);
                });
                pagination.html(res.links);
            });
        }

        function showErrors(errors) {
            $('.text-danger').text('');
            $('#formErrors').addClass('d-none').text('');
            Object.entries(errors).forEach(([key, msgArr]) => {
                $(`.error-${key}`).text(msgArr[0]);
            });
        }

        $('#createHotelBtn').click(() => {
            $('#hotelForm')[0].reset();
            $('#hotel_id').val('');
            $('#hotelForm').removeData('bulk');
            $('.text-danger').text('');
            $('#formErrors').addClass('d-none').text('');
            $('#hotelForm .col-md-6').show();
            modal.show();
        });

        $('#bulkEditBtn').click(() => {
            const selected = $('.hotelCheckbox:checked');
            if (selected.length === 0) return;

            $('#hotelForm')[0].reset();
            $('#hotel_id').val('');
            $('#hotelForm').data('bulk', true);
            $('.text-danger').text('');
            $('#formErrors').addClass('d-none').text('');

            // Only show specific fields
            $('#hotelForm .col-md-6').hide();
            ['#display_name', '#city_name', '#country_name', '#priority'].forEach(id => {
                $(id).closest('.col-md-6').show();
            });

            modal.show();
        });

        $('#hotelForm').submit(function (e) {
            e.preventDefault();

            const isBulk = $(this).data('bulk') === true;
            const formData = new FormData(this);
            const id = $('#hotel_id').val();
            let url = '/hotels';

            if (isBulk) {
                const selectedIds = $('.hotelCheckbox:checked').map((_, el) => $(el).val()).get();
                formData.append('bulk_ids', JSON.stringify(selectedIds));
                url = '/hotels/bulk-update';
            } else if (id) {
                url = `/hotels/${id}`;
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: () => {
                    modal.hide();
                    loadHotels();
                },
                error: xhr => {
                    $('.text-danger').text('');
                    $('#formErrors').addClass('d-none').text('');
                    if (xhr.status === 422) {
                        showErrors(xhr.responseJSON.errors);
                    } else {
                        $('#formErrors').removeClass('d-none').text('Error: ' + (xhr.responseJSON?.message || 'Unexpected error'));
                    }
                }
            });
        });

        $(document).on('click', '.editBtn', function () {
            const id = $(this).data('id');

            $.get(`/hotels/${id}`, function (data) {
                $('#hotelForm')[0].reset();
                $('#hotel_id').val(data.id);
                $('#hotelForm').removeData('bulk');
                $('.text-danger').text('');
                $('#hotelForm .col-md-6').show();

                Object.entries(data).forEach(([key, val]) => {
                    const field = $('#' + key);
                    if (field.length && field.attr('type') !== 'file') {
                        if (field.is('textarea')) {
                            field.text(val ?? '');
                        } else {
                            field.val(val ?? '');
                        }
                    }
                });

                modal.show();
            });
        });

        $(document).on('click', '.deleteBtn', function () {
            const id = $(this).data('id');
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            $('#confirmDelete').data('id', id);
            confirmModal.show();
        });

        $(document).on('click', '#confirmDelete', function () {
            const id = $(this).data('id');
            $.ajax({
                url: `/hotels/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: () => {
                    loadHotels();
                    bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
                },
                error: () => alert('Delete failed.')
            });
        });

        $(document).on('click', '#pagination a', function (e) {
            e.preventDefault();
            const page = $(this).attr('href').split('page=')[1];
            loadHotels(page);
        });

        // Checkbox select all
        $(document).on('change', '.hotelCheckbox, #selectAll', function () {
            const checkedCount = $('.hotelCheckbox:checked').length;
            $('#selectAll').prop('checked', $('.hotelCheckbox').length === checkedCount);
            $('#bulkEditBtn').prop('disabled', checkedCount === 0);
            $('#bulkDeleteBtn').prop('disabled', checkedCount === 0);
        });


        $('#selectAll').on('change', function () {
            $('.hotelCheckbox').prop('checked', this.checked).trigger('change');
        });
        $('#bulkDeleteBtn').click(() => {
            const selected = $('.hotelCheckbox:checked');
            if (selected.length === 0) return;
            new bootstrap.Modal(document.getElementById('bulkDeleteModal')).show();
        });

        $('#confirmBulkDelete').click(() => {
            const selectedIds = $('.hotelCheckbox:checked').map((_, el) => $(el).val()).get();

            $.ajax({
                url: '/hotels/bulk-delete',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: selectedIds
                },
                success: () => {
                    loadHotels();
                    bootstrap.Modal.getInstance(document.getElementById('bulkDeleteModal')).hide();
                },
                error: xhr => {
                    alert(xhr.responseJSON?.message || 'Bulk delete failed.');
                }
            });
        });

        loadHotels();
    </script>

    <!-- Delete Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this hotel?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endpush

<!-- Bulk Delete Confirm Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Bulk Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the selected hotels?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDelete">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>