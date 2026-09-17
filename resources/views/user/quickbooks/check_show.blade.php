@extends('layouts/layoutMaster')

@section('title', 'QuickBooks Check #' . $check->CheckNumber)

@section('vendor-style')
    <style>
        /* Select2 dropdown styling to match Receive/Send Payment */
        .select2-container--default .select2-selection--single {
            border: 1px solid !important;
            height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border: 1px solid !important;
        }

        html.select2-dropdown-open {
            overflow-x: hidden !important;
        }

        body.select2-dropdown-open {
            overflow-x: hidden !important;
        }

        .select2-container {
            max-width: 100%;
        }

        .select2-dropdown {
            max-width: 100vw !important;
            box-sizing: border-box !important;
        }

        .select2-results {
            max-width: 100% !important;
        }

        #payor-edit,
        #payee-edit {
            cursor: pointer;
            color: #7367f0;
            flex-shrink: 0;
        }
    </style>
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Check #{{ $check->CheckNumber }}</h4>
            <div class="text-muted">
                Status: <strong>{{ $check->Status }}</strong>
                @if ($check->CheckType === 'Process Payment')
                    <span class="badge bg-label-success ms-1">Receive Payment</span>
                @else
                    <span class="badge bg-label-primary ms-1">Send Payment</span>
                @endif
                @if ($check->check_number_conflict)
                    <span class="badge bg-label-warning ms-1">Check number conflict</span>
                @endif
                @if ($check->qbo_print_later)
                    <span class="badge bg-label-primary ms-1">Print later in QBO</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('qbo.checks') }}" class="btn btn-outline-secondary">Back</a>
            @if ($check->Status !== 'generated')
                @if ($check->CheckType === 'Make Payment')
                    <a href="{{ route('send_check_generate', $check->CheckID) }}"
                       id="generate-print-btn"
                       class="btn btn-primary"
                       data-generate-url="{{ route('send_check_generate', $check->CheckID) }}">Generate / Print</a>
                @else
                    <a href="{{ route('check_generate', $check->CheckID) }}"
                       id="generate-print-btn"
                       class="btn btn-primary"
                       data-generate-url="{{ route('check_generate', $check->CheckID) }}">Generate / Print</a>
                @endif
            @endif
        </div>
    </div>

    <div id="generate-error" class="alert alert-danger d-none" role="alert"></div>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($check->check_number_conflict)
        <div class="alert alert-warning">
            Warning: another check in your account already uses number <strong>{{ $check->CheckNumber }}</strong>.
            Review before printing.
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Details</h5></div>
                <div class="card-body">
                    <dl class="row mb-0 align-items-center">
                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">
                            {{ $check->CheckType === 'Process Payment' ? 'Receive Payment' : 'Send Payment' }}
                        </dd>

                        @if ($check->CheckType === 'Process Payment')
                            <dt class="col-sm-4 mb-3">Payor</dt>
                            <dd class="col-sm-8 mb-3">
                                <div class="d-flex align-items-center gap-1">
                                    <select id="payor" name="payor" class="form-control">
                                        <option value="">Select Pay From</option>
                                        @if (count($payors) > 0)
                                            <option value="add_new_payor" style="font-weight: bold;">Add New Payors</option>
                                            @foreach ($payors as $payor)
                                                @php
                                                    $name = $payor->Name;
                                                    if (!empty($payor->AccountNickname)) {
                                                        $name = $payor->Name . ' (' . $payor->AccountNickname . ')';
                                                    }
                                                @endphp
                                                <option value="{{ $payor->EntityID }}"
                                                    @selected((int) ($check->PayorID ?? 0) === (int) $payor->EntityID)>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                            <option value="add_new_payor" style="font-weight: bold;">Add New Payors</option>
                                        @else
                                            <option value="add_new_payor" style="font-weight: bold;">Add New Payors</option>
                                        @endif
                                    </select>
                                    <span id="payor-edit" class="{{ !empty($check->PayorID) ? '' : 'd-none' }}">
                                        <i class="ti ti-pencil me-1"></i>
                                    </span>
                                </div>
                            </dd>

                            <dt class="col-sm-4 mb-3">Payee</dt>
                            <dd class="col-sm-8 mb-3">
                                <div class="d-flex align-items-center gap-1">
                                    <select id="payee" name="payee" class="form-control">
                                        <option value="">Select Pay To</option>
                                        @if (count($payees) > 0)
                                            <option value="add_new_payee" style="font-weight: bold;">Add New Payee</option>
                                            @foreach ($payees as $payee)
                                                <option value="{{ $payee->EntityID }}"
                                                    @selected((int) ($check->PayeeID ?? 0) === (int) $payee->EntityID)>
                                                    {{ $payee->Name }}
                                                </option>
                                            @endforeach
                                            <option value="add_new_payee" style="font-weight: bold;">Add New Payee</option>
                                        @else
                                            <option value="add_new_payee" style="font-weight: bold;">Add New Payee</option>
                                        @endif
                                    </select>
                                    <span id="payee-edit" class="{{ !empty($check->PayeeID) ? '' : 'd-none' }}">
                                        <i class="ti ti-pencil me-1"></i>
                                    </span>
                                </div>
                            </dd>
                        @else
                            <dt class="col-sm-4 mb-3">Payee</dt>
                            <dd class="col-sm-8 mb-3">
                                <div class="d-flex align-items-center gap-1">
                                    <select id="payee" name="payee" class="form-control">
                                        <option value="">Select Pay To</option>
                                        @if (count($payees) > 0)
                                            <option value="add_new_payee" style="font-weight: bold;">Add New Payee</option>
                                            @foreach ($payees as $payee)
                                                <option value="{{ $payee->EntityID }}"
                                                    @selected((int) ($check->PayeeID ?? 0) === (int) $payee->EntityID)>
                                                    {{ $payee->Name }}
                                                </option>
                                            @endforeach
                                            <option value="add_new_payee" style="font-weight: bold;">Add New Payee</option>
                                        @else
                                            <option value="add_new_payee" style="font-weight: bold;">Add New Payee</option>
                                        @endif
                                    </select>
                                    <span id="payee-edit" class="{{ !empty($check->PayeeID) ? '' : 'd-none' }}">
                                        <i class="ti ti-pencil me-1"></i>
                                    </span>
                                </div>
                            </dd>

                            <dt class="col-sm-4 mb-3">Payor</dt>
                            <dd class="col-sm-8 mb-3">
                                <div class="d-flex align-items-center gap-1">
                                    <select id="payor" name="payor" class="form-control">
                                        <option value="">Select Pay From</option>
                                        @if (count($payors) > 0)
                                            <option value="add_new_payor" style="font-weight: bold;">Add New Payors</option>
                                            @foreach ($payors as $payor)
                                                @php
                                                    $name = $payor->Name;
                                                    if (!empty($payor->AccountNickname)) {
                                                        $name = $payor->Name . ' (' . $payor->AccountNickname . ')';
                                                    }
                                                @endphp
                                                <option value="{{ $payor->EntityID }}"
                                                    @selected((int) ($check->PayorID ?? 0) === (int) $payor->EntityID)>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                            <option value="add_new_payor" style="font-weight: bold;">Add New Payors</option>
                                        @else
                                            <option value="add_new_payor" style="font-weight: bold;">Add New Payors</option>
                                        @endif
                                    </select>
                                    <span id="payor-edit" class="{{ !empty($check->PayorID) ? '' : 'd-none' }}">
                                        <i class="ti ti-pencil me-1"></i>
                                    </span>
                                </div>
                            </dd>
                        @endif

                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8">${{ number_format((float) $check->Total, 2) }}</dd>
                        <dt class="col-sm-4">Issue date</dt>
                        <dd class="col-sm-8">{{ $check->IssueDate ? date('m/d/Y', strtotime($check->IssueDate)) : '—' }}</dd>
                        <dt class="col-sm-4">Memo</dt>
                        <dd class="col-sm-8">{{ $check->Memo ?: '—' }}</dd>
                        <dt class="col-sm-4">QBO Id</dt>
                        <dd class="col-sm-8">{{ $check->qbo_id ?: '—' }}</dd>
                        <dt class="col-sm-4">QBO company</dt>
                        <dd class="col-sm-8">{{ $check->qboCompany->name ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Category / line items</h5></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($check->lineItems as $line)
                                <tr>
                                    <td>{{ $line->line_no }}</td>
                                    <td>{{ $line->account_name ?: '—' }}</td>
                                    <td>{{ $line->description ?: '—' }}</td>
                                    <td class="text-end">${{ number_format((float) $line->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">No line items synced.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="payor_id" value="{{ $check->PayorID ?: '' }}">
    <input type="hidden" id="payee_id" value="{{ $check->PayeeID ?: '' }}">

    {{-- Payor modal --}}
    <div class="modal fade" id="payorModel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span id="payor_h">Add</span> Payor</h5>
                    <button type="button" class="btn-close" id="payor_close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-6" id="add-payor">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="account_nickname">Account nickname</label>
                            <input type="text" name="account_nickname" id="account_nickname" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email (optional)</label>
                            <input type="text" name="email" id="email" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="address1">Address</label>
                            <textarea id="address1" name="address1" class="form-control"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="city">City</label>
                            <input type="text" name="city" id="city" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="state">State</label>
                            <select name="state" id="state" class="form-control">
                                <option value="">-- Select State --</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state }}">{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="zip">Zip</label>
                            <input type="text" name="zip" id="zip" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="bank_name">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="account_number">Account Number</label>
                            <input type="text" inputmode="numeric" name="account_number" id="account_number" class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="routing_number">Routing Number</label>
                            <input type="text" name="routing_number" id="routing_number" class="form-control" maxlength="9"
                                   oninput="this.value = this.value.replace(/\D/g, '').slice(0,9);" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="add-payor-btn" type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Payee modal --}}
    <div class="modal fade" id="payeeModel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span class="payee_h" id="payee_h">Add</span> Payee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-6" id="add-payee">
                        <div class="col-md-{{ $category === 'SP' ? '6' : '12' }}">
                            <label class="form-label" for="payee-name">Name</label>
                            <input type="text" name="name" id="payee-name" class="form-control" value="">
                            <span class="text-danger" id="payee-name-error"></span>
                        </div>
                        @if ($category === 'SP')
                            <div class="col-md-6">
                                <label class="form-label" for="payee-email">Email <span class="text-danger">*</span></label>
                                <input type="text" name="email" id="payee-email" class="form-control" value="">
                                <span class="text-danger" id="payee-email-error"></span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="add-payee-btn" type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @vite(['resources/assets/js/ui-modals.js'])
    <script>
        $(document).ready(function () {
            const category = @json($category);
            const assignUrl = @json(route('qbo.checks.assignParty', ['id' => $check->CheckID]));
            const csrf = @json(csrf_token());
            let suppressAssign = false;

            function initSelect2(selector, placeholder) {
                var $select = $(selector);
                if (!$select.length) {
                    return;
                }
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                var currentVal = null;
                $select.find('option').each(function () {
                    if ($(this).attr('selected') && $(this).val() !== '') {
                        currentVal = $(this).val();
                        return false;
                    }
                });
                if (!currentVal || currentVal === '') {
                    currentVal = $select.val();
                }

                $select.select2({
                    placeholder: placeholder,
                    allowClear: false,
                    width: '100%',
                    minimumResultsForSearch: 0,
                    dropdownParent: $('body')
                });

                if (currentVal && currentVal !== '' && currentVal !== 'add_new_payor' && currentVal !== 'add_new_payee') {
                    $select.val(currentVal).trigger('change.select2');
                } else {
                    $select.val('').trigger('change.select2');
                }
            }

            setTimeout(function () {
                initSelect2('#payor', 'Select Pay From');
                initSelect2('#payee', 'Select Pay To');
            }, 100);

            var select2OpenTimeout = null;
            $(document).on('select2:open', function () {
                if (select2OpenTimeout) {
                    clearTimeout(select2OpenTimeout);
                    select2OpenTimeout = null;
                }
                $('html, body').addClass('select2-dropdown-open');
            });
            $(document).on('select2:close', function () {
                if (select2OpenTimeout) {
                    clearTimeout(select2OpenTimeout);
                }
                select2OpenTimeout = setTimeout(function () {
                    if ($('.select2-container--open').length === 0) {
                        $('html, body').removeClass('select2-dropdown-open');
                    }
                    select2OpenTimeout = null;
                }, 100);
            });

            function clearPayorErrors() {
                $('#add-payor').closest('.modal').find('.text-danger').remove();
            }

            function resetPayorForm() {
                $('#payor_id').val('');
                $('#add-payor #name').val('');
                $('#add-payor #account_nickname').val('');
                $('#add-payor #email').val('');
                $('#add-payor #address1').val('');
                $('#add-payor #city').val('');
                $('#add-payor #state').val('');
                $('#add-payor #zip').val('');
                $('#add-payor #bank_name').val('');
                $('#add-payor #account_number').val('');
                $('#add-payor #routing_number').val('');
                clearPayorErrors();
                $('#payor_h').text('Add');
            }

            function fillPayorForm(payor) {
                $('#payor_id').val(payor.EntityID);
                $('#add-payor #name').val(payor.Name || '');
                $('#add-payor #account_nickname').val(payor.AccountNickname || '');
                $('#add-payor #email').val(payor.Email || '');
                $('#add-payor #address1').val(payor.Address1 || '');
                $('#add-payor #city').val(payor.City || '');
                $('#add-payor #state').val(payor.State || '');
                $('#add-payor #zip').val(payor.Zip || '');
                $('#add-payor #bank_name').val(payor.BankName || '');
                $('#add-payor #account_number').val(payor.AccountNumber || '');
                $('#add-payor #routing_number').val(payor.RoutingNumber || '');
            }

            function resetPayeeForm() {
                $('#payee_id').val('');
                $('#payee-name').val('');
                $('#payee-email').val('');
                $('#payee-name-error').text('');
                $('#payee-email-error').text('');
                $('#payee_h').text('Add');
                $('.payee_h').text('Add');
            }

            function fillPayeeForm(payee) {
                $('#payee_id').val(payee.EntityID);
                $('#payee-name').val(payee.Name || '');
                if ($('#payee-email').length) {
                    $('#payee-email').val(payee.Email || '');
                }
            }

            function assignParty(party, entityId) {
                return $.ajax({
                    url: assignUrl,
                    method: 'POST',
                    data: {
                        _token: csrf,
                        party: party,
                        entity_id: entityId
                    }
                });
            }

            function upsertPayorOption(payor) {
                let displayName = payor.Name;
                if (payor.AccountNickname && String(payor.AccountNickname).trim() !== '') {
                    displayName = payor.Name + ' (' + payor.AccountNickname + ')';
                }
                const $existing = $('#payor option[value="' + payor.EntityID + '"]');
                if ($existing.length) {
                    $existing.text(displayName);
                } else {
                    const $addNew = $('#payor option[value="add_new_payor"]');
                    const opt = `<option value="${payor.EntityID}">${displayName}</option>`;
                    if ($addNew.length > 1) {
                        $addNew.last().before(opt);
                    } else if ($addNew.length === 1) {
                        $addNew.after(opt);
                    } else {
                        $('#payor').append(opt);
                    }
                }
                suppressAssign = true;
                $('#payor').val(String(payor.EntityID)).trigger('change.select2');
                suppressAssign = false;
                $('#payor-edit').removeClass('d-none');
            }

            function upsertPayeeOption(payee) {
                const $existing = $('#payee option[value="' + payee.EntityID + '"]');
                if ($existing.length) {
                    $existing.text(payee.Name);
                } else {
                    const $addNew = $('#payee option[value="add_new_payee"]');
                    const opt = `<option value="${payee.EntityID}">${payee.Name}</option>`;
                    if ($addNew.length > 1) {
                        $addNew.last().before(opt);
                    } else if ($addNew.length === 1) {
                        $addNew.after(opt);
                    } else {
                        $('#payee').append(opt);
                    }
                }
                suppressAssign = true;
                $('#payee').val(String(payee.EntityID)).trigger('change.select2');
                suppressAssign = false;
                $('#payee-edit').removeClass('d-none');
            }

            $('#payor').on('change', function () {
                const id = $(this).val();

                if (id === 'add_new_payor') {
                    $('#payor-edit').addClass('d-none');
                    resetPayorForm();
                    $('#payorModel').modal('show');
                    return;
                }

                if (!id) {
                    $('#payor-edit').addClass('d-none');
                    $('#payor_id').val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('get_payor', ':id') }}".replace(':id', id) + '?type=' + category,
                    method: 'GET',
                    success: function (response) {
                        if (response.payor) {
                            fillPayorForm(response.payor);
                            $('#payor-edit').removeClass('d-none');
                            if (!suppressAssign) {
                                assignParty('payor', response.payor.EntityID);
                            }
                        }
                    }
                });
            });

            $('#payee').on('change', function () {
                const id = $(this).val();

                if (id === 'add_new_payee') {
                    $('#payee-edit').addClass('d-none');
                    resetPayeeForm();
                    $('#payeeModel').modal('show');
                    return;
                }

                if (!id) {
                    $('#payee-edit').addClass('d-none');
                    $('#payee_id').val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('get_payee', ':id') }}".replace(':id', id) + '?type=' + category,
                    method: 'GET',
                    success: function (response) {
                        if (response.payee) {
                            fillPayeeForm(response.payee);
                            $('#payee-edit').removeClass('d-none');
                            if (!suppressAssign) {
                                assignParty('payee', response.payee.EntityID);
                            }
                        }
                    }
                });
            });

            $('#payor-edit').on('click', function (e) {
                e.preventDefault();
                const id = $('#payor').val() || $('#payor_id').val();
                if (!id || id === 'add_new_payor') {
                    return;
                }
                $.ajax({
                    url: "{{ route('get_payor', ':id') }}".replace(':id', id) + '?type=' + category,
                    method: 'GET',
                    success: function (response) {
                        if (response.payor) {
                            fillPayorForm(response.payor);
                            $('#payor_h').text('Edit');
                            $('#payorModel').modal('show');
                        }
                    }
                });
            });

            $('#payee-edit').on('click', function (e) {
                e.preventDefault();
                const id = $('#payee').val() || $('#payee_id').val();
                if (!id || id === 'add_new_payee') {
                    return;
                }
                $.ajax({
                    url: "{{ route('get_payee', ':id') }}".replace(':id', id) + '?type=' + category,
                    method: 'GET',
                    success: function (response) {
                        if (response.payee) {
                            fillPayeeForm(response.payee);
                            $('#payee_h').text('Edit');
                            $('.payee_h').text('Edit');
                            $('#payeeModel').modal('show');
                        }
                    }
                });
            });

            $('#payorModel').on('hidden.bs.modal', function () {
                if ($('#payor').val() === 'add_new_payor') {
                    suppressAssign = true;
                    $('#payor').val('').trigger('change.select2');
                    suppressAssign = false;
                    $('#payor-edit').addClass('d-none');
                }
            });

            $('#payeeModel').on('hidden.bs.modal', function () {
                if ($('#payee').val() === 'add_new_payee') {
                    suppressAssign = true;
                    $('#payee').val('').trigger('change.select2');
                    suppressAssign = false;
                    $('#payee-edit').addClass('d-none');
                }
            });

            $('#add-payor-btn').on('click', function (event) {
                event.preventDefault();
                const id = $('#payor_id').val();
                clearPayorErrors();

                $.ajax({
                    url: "{{ route('user.add-payor') }}",
                    method: 'POST',
                    data: {
                        _token: csrf,
                        name: $('#add-payor #name').val(),
                        account_nickname: $('#add-payor #account_nickname').val(),
                        email: $('#add-payor #email').val(),
                        address1: $('#add-payor #address1').val(),
                        city: $('#add-payor #city').val(),
                        state: $('#add-payor #state').val(),
                        zip: $('#add-payor #zip').val(),
                        bank_name: $('#add-payor #bank_name').val(),
                        account_number: $('#add-payor #account_number').val(),
                        routing_number: $('#add-payor #routing_number').val(),
                        type: 'Payor',
                        category: category,
                        id: id
                    },
                    success: function (response) {
                        if (response.errors) {
                            $.each(response.errors, function (key, value) {
                                var $wrap = $('#add-payor #' + key).closest('.col-md-6');
                                $wrap.find('.text-danger').remove();
                                $wrap.append('<span class="text-danger">' + value[0] + '</span>');
                            });
                            return;
                        }
                        if (response.success && response.payor) {
                            assignParty('payor', response.payor.EntityID).done(function () {
                                upsertPayorOption(response.payor);
                                $('#payor_id').val(response.payor.EntityID);
                                $('#payorModel').modal('hide');
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        alert('Unable to save payor.');
                    }
                });
            });

            $('#add-payee-btn').on('click', function (event) {
                event.preventDefault();
                const id = $('#payee_id').val();
                $('#payee-name-error').text('');
                $('#payee-email-error').text('');

                const formData = {
                    _token: csrf,
                    name: $('#payee-name').val(),
                    type: 'Payee',
                    category: category,
                    id: id
                };
                if ($('#payee-email').length) {
                    formData.email = $('#payee-email').val();
                }

                $.ajax({
                    url: "{{ route('user.add-payee') }}",
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.errors) {
                            $.each(response.errors, function (key, value) {
                                if (key === 'name') {
                                    $('#payee-name-error').text(value[0]);
                                } else if (key === 'email') {
                                    $('#payee-email-error').text(value[0]);
                                }
                            });
                            return;
                        }
                        if (response.success && response.payee) {
                            assignParty('payee', response.payee.EntityID).done(function () {
                                upsertPayeeOption(response.payee);
                                $('#payee_id').val(response.payee.EntityID);
                                $('#payeeModel').modal('hide');
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        alert('Unable to save payee.');
                    }
                });
            });

            function showGenerateError(message) {
                $('#generate-error').removeClass('d-none').text(message);
                $('html, body').animate({ scrollTop: 0 }, 200);
            }

            function hideGenerateError() {
                $('#generate-error').addClass('d-none').text('');
            }

            function isBlank(value) {
                return value === null || value === undefined || String(value).trim() === '';
            }

            function missingPayorFields(payor) {
                const required = {
                    Name: 'Name',
                    AccountNickname: 'Account Nickname',
                    Address1: 'Address',
                    City: 'City',
                    State: 'State',
                    Zip: 'Zip',
                    BankName: 'Bank Name',
                    AccountNumber: 'Account Number',
                    RoutingNumber: 'Routing Number'
                };
                const missing = [];
                $.each(required, function (key, label) {
                    if (isBlank(payor[key])) {
                        missing.push(label);
                    }
                });
                return missing;
            }

            $('#generate-print-btn').on('click', function (e) {
                e.preventDefault();
                hideGenerateError();

                const generateUrl = $(this).data('generate-url');
                const payorId = $('#payor').val();
                const payeeId = $('#payee').val();

                if (!payorId || payorId === 'add_new_payor') {
                    showGenerateError('Please select a Payor and complete all required Payor fields before Generate / Print.');
                    return;
                }
                if (!payeeId || payeeId === 'add_new_payee') {
                    showGenerateError('Please select a Payee before Generate / Print.');
                    return;
                }

                $.ajax({
                    url: "{{ route('get_payor', ':id') }}".replace(':id', payorId) + '?type=' + category,
                    method: 'GET'
                }).done(function (payorRes) {
                    if (!payorRes.payor) {
                        showGenerateError('Selected Payor was not found. Please select or update Payor.');
                        return;
                    }
                    const missing = missingPayorFields(payorRes.payor);
                    if (missing.length) {
                        showGenerateError(
                            'Payor details are incomplete. Please fill: ' + missing.join(', ') +
                            ' (use the pencil icon), then try Generate / Print again.'
                        );
                        return;
                    }

                    $.ajax({
                        url: "{{ route('get_payee', ':id') }}".replace(':id', payeeId) + '?type=' + category,
                        method: 'GET'
                    }).done(function (payeeRes) {
                        if (!payeeRes.payee || isBlank(payeeRes.payee.Name)) {
                            showGenerateError('Please select a valid Payee before Generate / Print.');
                            return;
                        }
                        if (category === 'SP' && isBlank(payeeRes.payee.Email)) {
                            showGenerateError('Payee email is required. Please update Payee with the pencil icon.');
                            return;
                        }
                        window.location.href = generateUrl;
                    }).fail(function () {
                        showGenerateError('Unable to verify Payee. Please try again.');
                    });
                }).fail(function () {
                    showGenerateError('Unable to verify Payor. Please try again.');
                });
            });
        });
    </script>
@endsection
