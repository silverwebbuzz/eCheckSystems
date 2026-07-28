@extends('layouts/layoutMaster')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/swiper/swiper.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss'])
@endsection

@section('page-style')
    <!-- Page -->
    @vite(['resources/assets/vendor/scss/pages/cards-advance.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/swiper/swiper.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/dashboards-analytics.js'])
    @vite('resources/assets/js/app-academy-dashboard.js')
@endsection

@section('content')

    <div class="row g-6">
        <!-- Average Daily Sales -->
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Customers</h5>
                    <p class="mb-0 text-body">Total Number of Customers</p>
                    <h4 class="mb-0">{{ $total_users }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">This Month Revenue</h5>
                    <p class="mb-0 text-body">Total This Month Revenue</p>
                    <h4 class="mb-0">${{ $month_revanue }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Lifetime Revenue</h5>
                    <p class="mb-0 text-body">Total Lifetime Revenue</p>
                    <h4 class="mb-0">${{ $total_revanue }}</h4>
                </div>
            </div>
        </div>
        <!--/ Average Daily Sales -->

        <!-- Sales Overview -->
        {{-- <div class="col-xl-3 col-sm-6">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <p class="mb-0 text-body">Sales Overview</p>
                        <p class="card-text fw-medium text-success">+18.2%</p>
                    </div>
                    <h4 class="card-title mb-1">$42.5k</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="d-flex gap-2 align-items-center mb-2">
                                <span class="badge bg-label-info p-1 rounded"><i
                                        class="ti ti-shopping-cart ti-sm"></i></span>
                                <p class="mb-0">Order</p>
                            </div>
                            <h5 class="mb-0 pt-1">62.2%</h5>
                            <small class="text-muted">6,440</small>
                        </div>
                        <div class="col-4">
                            <div class="divider divider-vertical">
                                <div class="divider-text">
                                    <span class="badge-divider-bg bg-label-secondary">VS</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="d-flex gap-2 justify-content-end align-items-center mb-2">
                                <p class="mb-0">Visits</p>
                                <span class="badge bg-label-primary p-1 rounded"><i class="ti ti-link ti-sm"></i></span>
                            </div>
                            <h5 class="mb-0 pt-1">25.5%</h5>
                            <small class="text-muted">12,749</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-6">
                        <div class="progress w-100" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: 70%" role="progressbar" aria-valuenow="70"
                                aria-valuemin="0" aria-valuemax="100"></div>
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 30%" aria-valuenow="30"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        <!--/ Sales Overview -->

        <!-- Earning Reports -->
        {{-- <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header pb-0 d-flex justify-content-between">
                    <div class="card-title mb-0">
                        <h5 class="mb-1">Earning Reports</h5>
                        <p class="card-subtitle">Weekly Earnings Overview</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button"
                            id="earningReportsId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-dots-vertical ti-md text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="earningReportsId">
                            <a class="dropdown-item" href="javascript:void(0);">View More</a>
                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                        </div>
                    </div>
                    <!-- </div> -->
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-md-8">
                        <div class="col-12 col-md-5 d-flex flex-column">
                            <div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
                                <h2 class="mb-0">$468</h2>
                                <div class="badge rounded bg-label-success">+4.2%</div>
                            </div>
                            <small class="text-body">You informed of this week compared to last week</small>
                        </div>
                        <div class="col-12 col-md-7 ps-xl-8">
                            <div id="weeklyEarningReports"></div>
                        </div>
                    </div>
                    <div class="border rounded p-5 mt-5">
                        <div class="row gap-4 gap-sm-0">
                            <div class="col-12 col-sm-4">
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="badge rounded bg-label-primary p-1"><i
                                            class="ti ti-currency-dollar ti-sm"></i></div>
                                    <h6 class="mb-0 fw-normal">Earnings</h6>
                                </div>
                                <h4 class="my-2">$545.69</h4>
                                <div class="progress w-75" style="height:4px">
                                    <div class="progress-bar" role="progressbar" style="width: 65%" aria-valuenow="65"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="badge rounded bg-label-info p-1"><i class="ti ti-chart-pie-2 ti-sm"></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">Profit</h6>
                                </div>
                                <h4 class="my-2">$256.34</h4>
                                <div class="progress w-75" style="height:4px">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 50%"
                                        aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="badge rounded bg-label-danger p-1"><i class="ti ti-brand-paypal ti-sm"></i>
                                    </div>
                                    <h6 class="mb-0 fw-normal">Expense</h6>
                                </div>
                                <h4 class="my-2">$74.19</h4>
                                <div class="progress w-75" style="height:4px">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 65%"
                                        aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="col-xxl-4 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Packages</h5>
                    {{-- <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-2 me-n1" type="button"
                            id="assignmentProgress" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-dots-vertical ti-md text-muted"></i>
                        </button>
                    </div> --}}
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        @foreach ($package_data as $package)
                            <li class="d-flex mb-6">
                                <div class="chart-progress me-4" data-color="primary"
                                    data-series="{{ !empty($package['total_count']) ? round(($package['total_count'] / $total_package_count) * 100) : '0' }}"
                                    data-progress_variant="true">
                                </div>
                                <div class="row w-100 align-items-center">
                                    <div class="col-9">
                                        <div class="me-2">
                                            <h6 class="mb-2">{{ $package['name'] }}</h6>
                                            <small>{{ $package['total_count'] }} Customers </small>
                                        </div>
                                    </div>
                                    {{-- <div class="col-3 text-end">
                                        <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                                            <i class="ti ti-chevron-right scaleX-n1-rtl"></i>
                                        </button>
                                    </div> --}}
                                </div>
                            </li>
                        @endforeach
                        {{-- <li class="d-flex mb-6">
                            <div class="chart-progress me-4" data-color="primary"
                                data-series="{{ !empty($total_basic) ? round(($total_basic / $total_users) * 100) : '0' }}"
                                data-progress_variant="true">
                            </div>
                            <div class="row w-100 align-items-center">
                                <div class="col-9">
                                    <div class="me-2">
                                        <h6 class="mb-2">Basic Package</h6>
                                        <small>{{ $total_basic }} Tasks</small>
                                    </div>
                                </div>
                                <div class="col-3 text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                                        <i class="ti ti-chevron-right scaleX-n1-rtl"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-6">
                            <div class="chart-progress me-4" data-color="success"
                                data-series="{{ !empty($total_silver) ? round(($total_silver / $total_users) * 100) : '0' }}"
                                data-progress_variant="true">
                            </div>
                            <div class="row w-100 align-items-center">
                                <div class="col-9">
                                    <div class="me-2">
                                        <h6 class="mb-2">Silver Package</h6>
                                        <small>{{ $total_silver }} Tasks</small>
                                    </div>
                                </div>
                                <div class="col-3 text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                                        <i class="ti ti-chevron-right scaleX-n1-rtl"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-6">
                            <div class="chart-progress me-4" data-color="danger"
                                data-series="{{ !empty($total_gold) ? round(($total_gold / $total_users) * 100) : '0' }}"
                                data-progress_variant="true"></div>
                            <div class="row w-100 align-items-center">
                                <div class="col-9">
                                    <div class="me-2">
                                        <h6 class="mb-2">Gold Package</h6>
                                        <small>{{ $total_gold }} Tasks</small>
                                    </div>
                                </div>
                                <div class="col-3 text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary">
                                        <i class="ti ti-chevron-right scaleX-n1-rtl"></i>
                                    </button>
                                </div>
                            </div>
                        </li> --}}
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
