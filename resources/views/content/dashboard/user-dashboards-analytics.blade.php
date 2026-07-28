@extends('layouts/layoutMaster')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/swiper/swiper.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss'])
@endsection

@section('page-style')
    <!-- Page -->
    @vite(['resources/assets/vendor/scss/pages/cards-advance.scss'])
    <style>
        .ti-copy {
            cursor: pointer;
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/swiper/swiper.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/dashboards-analytics.js'])
    @vite('resources/assets/js/app-academy-dashboard.js')

    <script>
        $(document).ready(function() {
            $('#webFormTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('get_web_forms') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    },
                    {
                        data: 'page_url',
                        name: 'page_url'
                    },
                    {
                        render: function(data, type, row) {
                            return `<div class="d-flex gap-2">
                                        <i class="ti ti-copy copyButton" data-url=" `+ row.url +`
                                        " onclick="copyToClipboard(this)"></i></div>`;
                        }
                    }
                ]
            });
        });

        function copyToClipboard(element) {
            document.querySelectorAll('.copyButton').forEach(btn => {
                // btn.innerHTML = '<i class="ti ti-copy"></i>';
                btn.classList.remove('ti-check');
                btn.classList.remove('text-success');
                btn.classList.add('ti-copy');
                if (btn.nextElementSibling) {
                    btn.nextElementSibling.remove();
                }
                // btn.classList.remove('ti-check');
            });

            const text = element.getAttribute('data-url');
            navigator.clipboard.writeText(text).then(() => {
                element.classList.remove('ti-copy');
                element.classList.add('ti-check');
                element.classList.add('text-success');
                element.insertAdjacentHTML('afterend', '<label class="text-success">Copied!</label>');
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    </script>
@endsection

@section('content')
@if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
    @php
        $progress = !empty($package_data['remainingDays'])
            ? ($package_data['remainingDays'] * 100) / $package_data['total_days']
            : 0;
    @endphp
    <div class="row g-6">
        <!-- Average Daily Sales -->
        {{-- <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Companies</h5>
                    <p class="mb-0 text-body">Total Number of Companies</p>
                    <h4 class="mb-0">{{ $total_companies }}</h4>
                </div>
            </div>
        </div> --}}
        <div class="col-xxl-12">
            <div class="card mb-6">
                <h5 class="card-header">Current Plan</h5>
                <div class="card-body">
                    <div class="row row-gap-4 row-gap-xl-0">
                        <div class="col-xl-6 order-1 order-xl-0">
                            <div class="mb-4">
                                <h6 class="mb-1">Your Current Plan is
                                    {{ $current_package_id == '-1' ? 'Trial' : $package_data['package_name'] }}</h6>
                            </div>
                            
                            @if ($current_package_id != '-1')
                                <div class="mb-4">
                                    <h6 class="mb-1">Active until {{ $package_data['expiryDate'] }}</h6>
                                    <!-- <p>We will send you a notification upon Subscription expiration</p> -->
                                </div>
                            @endif
                            <button class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                                data-bs-target="#onboardingHorizontalSlideModal">
                                Upgrade Plan
                            </button>
                        </div>
                        @if ($current_package_id != '-1')
                            <div class="col-xl-6 order-0 order-xl-0">
                                <div class="plan-statistics">
                                    <div class="d-flex justify-content-between">
                                        <!-- <h6 class="mb-1">Days</h6>
                                                <h6 class="mb-1">{{ $package_data['remainingDays'] }} of
                                                    {{ $package_data['total_days'] }} Days</h6> -->
                                        <h6 class="mb-1"></h6>
                                        <h6 class="mb-1"> {{ $package_data['remainingDays'] }} days left until new billing
                                            cycle</h6>
                                        <!-- <h6 class="mb-1">{{ $package_data['remainingDays'] }} of
                                                        {{ $package_data['total_days'] }} Days</h6> -->
                                    </div>
                                    <div class="progress mb-1 bg-label-primary" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0"
                                            aria-valuemax="100" style="width: {{ $progress }}%"></div>
                                    </div>
                                    @if ($remaining_checks <= 0 && $package->CheckLimitPerMonth != 0)
                                        <small class="text-danger">Your plan requires update</small>
                                    @endif
                                    @if (!empty($paymentSubscription->NextPackageID))
                                        <div class="alert alert-warning mt-3" role="alert">
                                            Your subscription plan downgrade has been scheduled. The change will take effect
                                            on
                                            after your current plan expires. You can continue to enjoy your current plan
                                            benefits
                                            until then
                                        </div>
                                    @endif
                                    @if ($paymentSubscription->Status == 'Canceled')
                                        <div class="alert alert-danger mt-3" role="alert">
                                            Your subscription cancellation has been scheduled. The change will take effect
                                            after
                                            your current plan ends. You will continue to enjoy your current plan benefits
                                            until
                                            then.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header pb-0 px-2 d-flex flex-column justify-content-around">
                    <h5 class="mb-3 card-title">Total Checks in Plan</h5>
                    <h4 class="mb-0">{{ $given_checks }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header pb-0 px-2 d-flex flex-column justify-content-around">
                    <h5 class="mb-3 card-title">Total Checks Received</h5>
                    <h4 class="mb-0">{{ $checks_received ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header pb-0 px-2 d-flex flex-column justify-content-around">
                    <h5 class="mb-3 card-title">Total Checks Sent</h5>
                    <h4 class="mb-0">{{ $checks_sent ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header pb-0 px-2 d-flex flex-column justify-content-around">
                    <h5 class="mb-3 card-title">Remaining Checks</h5>
                    <h4 class="mb-0">{{ $remaining_checks }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Pay From</h5>
                    <p class="mb-0 text-body">Total Number of Payors</p>
                    <h4 class="mb-0">{{ $total_vendor }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Pay To</h5>
                    <p class="mb-0 text-body">Total Number of Payees</p>
                    <h4 class="mb-0">{{ $total_client }}</h4>
                </div>
            </div>
        </div>
    </div>
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
    <div class="card mt-5">
        <div class="card-header">
            <h5 class="card-title">Webforms</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="webFormTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">SrNo</th>
                            <th scope="col">Company Name</th>
                            <th scope="col">Page URL</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
