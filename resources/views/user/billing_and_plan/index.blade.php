@extends('layouts/layoutMaster')

@section('title', 'Account settings - Pages')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
    <style>
        #card-element {
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background-color: #f9fafb;
            transition: border 0.3s;
        }

        #card-element.StripeElement--focus {
            border-color: #6366f1;
            background-color: #fff;
        }

        #card-element.StripeElement--invalid {
            border-color: #dc3545;
        }

        #card-errors {
            margin-top: 8px;
            font-size: 0.875rem;
            color: #dc3545;
        }

        .btn-custom {
            background-color: #6366f1;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: #4f46e5;
        }
        
    </style>
@endsection
<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/pages-pricing.js', 'resources/assets/js/pages-account-settings-billing.js', 'resources/assets/js/app-invoice-list.js', 'resources/assets/js/modal-edit-cc.js', 'resources/assets/js/ui-modals.js'])

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        var key = "{{ env('STRIPE_PUBLIC') }}";
        const stripe = Stripe(key);
        var elements = stripe.elements();
        var card = elements.create('card');
        card.mount('#card-element');

        $(document).ready(function() {
            $('#invoice_data').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user_invoice') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'PaymentHistoryID', // Hidden ID column for sorting
                        name: 'PaymentHistoryID',
                        visible: false // Hides the ID column
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'PaymentStatus',
                        name: 'PaymentStatus',
                    },
                    {
                        data: 'PaymentAmount',
                        name: 'PaymentAmount',
                    },
                    {
                        data: 'PaymentDate',
                        name: 'PaymentDate'
                    },
                ]
            });

            $('#cardForm').on('submit', function(e) {
                e.preventDefault(); // Prevent the form from submitting normally

                // stripe.createToken(card).then(function(result) {
                //     if (result.error) {
                //         // If there's an error, display it
                //         alert(result.error.message);
                //     } else {
                //         var stripeToken = result.token.id;

                //         // If token creation is successful, append it to the form as a hidden input
                //         $('<input>').attr({
                //             type: 'hidden',
                //             name: 'stripeToken',
                //             value: stripeToken
                //         }).appendTo('#cardForm');

                //         // Submit the form with the stripeToken
                //         $('#cardForm')[0].submit();
                //     }
                // });
                stripe.createPaymentMethod({
                    type: 'card',
                    card: card,
                }).then(function(result) {
                    if (result.error) {
                        // Show error in your UI
                        alert(result.error.message);
                    } else {
                        // Append payment method ID to form and submit
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'payment_method', // must match server param
                            value: result.paymentMethod.id
                        }).appendTo('#cardForm');

                        $('#cardForm')[0].submit();
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    @php
        $progress = $package_id != '-1' ? ($package_data['remainingDays'] * 100) / $package_data['total_days'] : 0;
    @endphp
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            <script>
                setTimeout(function() {
                    window.location.reload();
                },1000);
            </script>
        @endif
    <div class="row">
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <div class="col-md-12">
            <div class="card mb-6">
                <!-- Current Plan -->
                <h5 class="card-header">Current Plan</h5>
                <div class="card-body">
                    <div class="row row-gap-6">
                        <div class="col-md-6 mb-1">
                            <div class="mb-6">
                                <h6 class="mb-1">Your Current Plan is
                                    {{ $package_id == '-1' ? 'Trial' : $package_data['package_name'] }}</h6>
                            </div>
                            @if ($package_id != '-1')
                                <div class="mb-6">
                                    <h6 class="mb-1">Active until {{ $package_data['expiryDate'] }}</h6>
                                    <p>We will send you a notification upon Subscription expiration</p>
                                </div>
                            @endif
                        </div>
                        @if ($package_id != '-1')
                            <div class="col-md-6">
                                @if ($package_data['RemainingChecks'] <= 0 && $package_data['is_unlimited'] == false)
                                    <div class="alert alert-warning mb-6" role="alert">
                                        <h5 class="alert-heading mb-1 d-flex align-items-center">
                                            <span class="alert-icon rounded"><i
                                                    class="ti ti-alert-triangle ti-md"></i></span>
                                            <span>We need your attention!</span>
                                        </h5>
                                        <span class="ms-11 ps-1">Your plan requires update</span>
                                    </div>
                                @endif
                                <div class="plan-statistics">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Days</h6>
                                        <h6 class="mb-1">{{ $package_data['remainingDays'] }} of
                                            {{ $package_data['total_days'] }} Days</h6>
                                    </div>
                                    <div class="progress rounded mb-1">
                                        <div class="progress-bar rounded" role="progressbar" aria-valuenow="25"
                                            aria-valuemin="0" aria-valuemax="100" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <small>{{ $package_data['remainingDays'] }} days remaining
                                        until your plan requires
                                        update</small>
                                </div>
                                @if (!empty($paymentSubscription->NextPackageID))
                                    <div class="alert alert-warning mt-3" role="alert">
                                        Your subscription plan downgrade has been scheduled. The change will take effect on
                                        after your current plan expires. You can continue to enjoy your current plan
                                        benefits
                                        until then
                                    </div>
                                @endif
                                @if ($paymentSubscription->Status == 'Active' && $paymentSubscription->CancelAt != null)
                                    <div class="alert alert-danger mt-3" role="alert">
                                        Your subscription cancellation has been scheduled. The change will take effect after
                                        your current plan ends. You will continue to enjoy your current plan benefits until
                                        then.
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="col-12 d-flex gap-2 flex-wrap">
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#onboardingHorizontalSlideModal">
                                @if ($package_id != '-1')
                                    Change
                                @else
                                    Select
                                @endif
                                Plan
                            </button>
                            @if (empty($package_data['cancel_plan']) && $package_id != '-1')
                                <a class="btn btn-label-danger " href="{{ route('user_cancel_plan') }}">Cancel
                                    Subscription</a>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /Current Plan -->
            </div>
            <div class="card mb-6">
                @if (session('success_card'))
                    <div class="alert alert-success">
                        {{ session('success_card') }}
                    </div>
                @endif
                @if (session('error_card'))
                    <div class="alert alert-danger">
                        {{ session('error_card') }}
                    </div>
                @endif
                @if($package_id != '-1');
                    <h5 class="card-header">Payment Methods</h5>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <form id="cardForm" class="row g-6" method="POST" action="{{ route('stripe.add_card') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="card-element" class="form-label">Card Details</label>
                                        <div id="card-element"><!-- Stripe Element mounts here --></div>
                                        <div id="card-errors" role="alert"></div>
                                    </div>
                                    <button type="submit" class="btn btn-custom w-100 mt-3">Save Card</button>
                            </div>
                            </form>

                        </div>
                        <div class="col-md-12 mt-5 mt-md-0">
                            @if (!empty($cards['data']))
                                <h5 class="mb-6">My Cards</h5>
                                <div class="added-cards">
                                    @foreach ($cards['data'] as $card)
                                        <div class="cardMaster p-6 bg-lighter rounded mb-6">
                                            <div class="d-flex justify-content-between flex-sm-row flex-column">
                                                <div class="card-information me-2">
                                                    <img class="mb-2 img-fluid"
                                                        src="{{ asset('assets/img/icons/payments/' . $card['card']['brand'] . '.png') }}"
                                                        alt="Master Card">
                                                    <div class="d-flex align-items-center mb-2 flex-wrap gap-2">
                                                        <h6 class="mb-0 me-2">{{ $card['billing_details']['name'] }}</h6>
                                                    </div>
                                                    <span class="card-number">&#8727;&#8727;&#8727;&#8727;
                                                        &#8727;&#8727;&#8727;&#8727; {{ $card['card']['last4'] }}</span>
                                                </div>
                                                <div class="d-flex flex-column text-start text-lg-end">
                                                    <div class="d-flex order-sm-0 order-1 mt-sm-0 mt-4">
                                                        @php
                                                            $isOnlyCard = count($cards['data']) === 1;
                                                            $isDefault =
                                                                (!empty($default_card) && $default_card == $card['id']) ||
                                                                ($isOnlyCard && empty($default_card));
                                                        @endphp

                                                        @if ($isDefault)
                                                            <button class="btn btn-sm btn-label-info">Default</button>
                                                        @else
                                                            <a href="{{ route('stripe.set_default', ['id' => $card['id']]) }}"
                                                                class="btn btn-sm btn-label-info">Set Default</a>
                                                        @endif
                                                        <a href="{{ route('stripe.delete_card', ['id' => $card['id']]) }}"
                                                            class="btn btn-sm btn-label-danger">Delete</a>
                                                    </div>
                                                    <small class="mt-sm-4 mt-2 order-sm-1 order-0">Card expires at
                                                        {{ $card['card']['exp_month'] }}/{{ $card['card']['exp_year'] }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <!-- Modal -->
                            @include('_partials/_modals/modal-edit-cc')
                            <!--/ Modal -->
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @if (false)
            <div class="card mb-6">
                <!-- Billing Address -->
                <h5 class="card-header">Billing Address</h5>
                <div class="card-body">
                    <form id="formAccountSettings" onsubmit="return false">
                        <div class="row">
                            <div class="mb-4 col-sm-6">
                                <label for="companyName" class="form-label">Company Name</label>
                                <input type="text" id="companyName" name="companyName" class="form-control"
                                    placeholder="{{ config('variables.creatorName') }}" />
                            </div>
                            <div class="mb-4 col-sm-6">
                                <label for="billingEmail" class="form-label">Billing Email</label>
                                <input class="form-control" type="text" id="billingEmail" name="billingEmail"
                                    placeholder="john.doe@example.com" />
                            </div>
                            <div class="mb-4 col-sm-6">
                                <label for="taxId" class="form-label">Tax ID</label>
                                <input type="text" id="taxId" name="taxId" class="form-control"
                                    placeholder="Enter Tax ID" />
                            </div>
                            <div class="mb-4 col-sm-6">
                                <label for="vatNumber" class="form-label">VAT Number</label>
                                <input class="form-control" type="text" id="vatNumber" name="vatNumber"
                                    placeholder="Enter VAT Number" />
                            </div>
                            <div class="mb-4 col-sm-6">
                                <label for="mobileNumber" class="form-label">Mobile</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">US (+1)</span>
                                    <input class="form-control mobile-number" type="text" id="mobileNumber"
                                        name="mobileNumber" placeholder="202 555 0111" />
                                </div>
                            </div>
                            <div class="mb-4 col-sm-6">
                                <label for="country" class="form-label">Country</label>
                                <select id="country" class="form-select select2" name="country">
                                    <option selected>USA</option>
                                    <option>Canada</option>
                                    <option>UK</option>
                                    <option>Germany</option>
                                    <option>France</option>
                                </select>
                            </div>
                            <div class="mb-4 col-12">
                                <label for="billingAddress" class="form-label">Billing Address</label>
                                <input type="text" class="form-control" id="billingAddress" name="billingAddress"
                                    placeholder="Billing Address" />
                            </div>
                            <div class="mb-4 col-sm-6">
                                <label for="state" class="form-label">State</label>
                                <input class="form-control" type="text" id="state" name="state"
                                    placeholder="California" />
                            </div>
                            <div class="mb-4 col-sm-6">
                                <label for="zipCode" class="form-label">Zip Code</label>
                                <input type="text" class="form-control zip-code" id="zipCode" name="zipCode"
                                    placeholder="231465" maxlength="6" />
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-3">Save changes</button>
                            <button type="reset" class="btn btn-label-secondary">Discard</button>
                        </div>
                    </form>
                </div>
                <!-- /Billing Address -->
            </div>
        @endif
    </div>
    @if ($package_id != '-1')
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header">Payments</h5>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="table" id="invoice_data">
                        <thead>
                            <tr>
                                <th class="d-none">ID</th>
                                <th>#</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="modal-onboarding modal fade animate__animated" id="onboardingHorizontalSlideModal" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content text-center">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="pricing-table">
                    @if (count($packages) > 0)
                        @foreach ($packages as $package)
                            <div
                                class="pricing-card {{ $package->Name == 'PRO' || $package->Name == 'ENTERPRISE' ? 'popular' : '' }}{{ $user->CurrentPackageID != '-1' && $user->CurrentPackageID == $package->PackageID ? ' selected-plan' : '' }}">
                                <h3>{{ $package->Name }}</h3>
                                @if ($package->Duration < 30)
                                    <p class="price">${{ $package->Price }} <span>({{ $package->Duration }} days)</span>
                                    </p>
                                @else
                                    <p class="price">${{ $package->Price }} <span>monthly</span></p>
                                @endif
                                <ul class="features">
                                    @if ($package->Duration < 30)
                                        <li>Up to
                                            {{ $package->Name != 'UNLIMITED' ? $package->CheckLimitPerMonth : 'Unlimited ' }}
                                            checks
                                            / {{ $package->Duration }} days</li>
                                    @else
                                        <li>Up to
                                            {{ $package->Name != 'UNLIMITED' ? $package->CheckLimitPerMonth : 'Unlimited ' }}
                                            checks
                                            / month</li>
                                    @endif
                                    <li>Email Support</li>
                                    <li>Unlimited Users</li>
                                    @if ($package->Name != 'BASIC')
                                        <li>Custom Webform*</li>
                                    @endif
                                    <li>3 mos History Storage</li>
                                </ul>
                                @if ($user->CurrentPackageID != '-1' && $user->CurrentPackageID == $package->PackageID)
                                    <p class="current-plan">Current Plan</p>
                                @else
                                    @if ($user->CurrentPackageID != '-1')
                                        <a href="{{ route('user.select-package', ['id' => $user->UserID, 'plan' => $package->PackageID]) }}"
                                            class="plan-button" onclick="$('#payment-loader').show();">Select Plan</a>
                                    @else
                                        <a href="{{ route('user-select-package', ['id' => $user->UserID, 'plan' => $package->PackageID]) }}"
                                            class="plan-button" onclick="$('#payment-loader').show();">Select Plan</a>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
