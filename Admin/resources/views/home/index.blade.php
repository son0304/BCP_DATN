@extends('app')
<style>
  body{
    color: white !important;
  }
  .card-link{
    text-decoration: none;
    color: inherit;
    display: block;
    width: 100%;
  }
  .card-link .cart{
    transition: transform 0.2s ease-in-out;
  }
  .card-link:hover .card{
    transform: translateT(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0,1)
  }
</style>
@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span> Dashboard
            </h3>
            {{-- <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                        <span></span>Overview <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
                    </li>
                </ul>
            </nav> --}}
        </div>
        <div class="row">
            <div class="col-md-4 stretch-card grid-margin">
                <a href="{{ route('admin.venues.index') }}" class="card-link">
                    <div class="card bg-gradient-danger card-img-holder text-white">
                        <div class="card-body">
                            <img src="{{ asset('dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute"
                                alt="circle-image" />
                            <h4 class="font-weight-normal mb-3">Courts <i class="mdi mdi-stadium mdi-24px float-end"></i>
                            </h4>
                            <h2 class="mb-5">{{ number_format($totalVenueCount) }}</h2>
                            <h6 class="card-text"><b>{{ $activeVenueCount }}</b> sân đang hoạt động</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <a href="{{ route('admin.promotions.index') }}" class="card-link">
                    <div class="card bg-gradient-info card-img-holder text-white">
                        <div class="card-body">
                            <img src="{{ asset('dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute"
                                alt="circle-image" />
                            <h4 class="font-weight-normal mb-3">Voucher <i
                                    class="mdi mdi-ticket-percent mdi-24px float-end"></i>
                            </h4>
                            <h2 class="mb-5">{{ number_format($totalPromotionCount) }}</h2>
                            <h6 class="card-text">Đã sử dụng: {{ $usedPromotionCount }}/{{ $totalPromotionCount }}</h6>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <a href="" class="card-link">
                    <div class="card bg-gradient-success card-img-holder text-white">
                        <div class="card-body">
                            <img src="{{ asset('dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute"
                                alt="circle-image" />
                            <h4 class="font-weight-normal mb-3">User <i class="mdi mdi-account mdi-24px float-end"></i>
                            </h4>
                            <h2 class="mb-5">{{ number_format($totalUserCount) }}</h2>
                            <h6 class="card-text"><b>+ {{ $newUserThisMonth }}</b> người dùng mới trong tháng</h6>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-7 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="clearfix">
                            <h4 class="card-title float-start">Visit And Sales Statistics</h4>
                            <div id="visit-sale-chart-legend"
                                class="rounded-legend legend-horizontal legend-top-right float-end"></div>
                        </div>
                        <canvas id="visit-sale-chart" class="mt-4"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-5 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Traffic Sources</h4>
                        <div class="doughnutjs-wrapper d-flex justify-content-center">
                            <canvas id="traffic-chart"></canvas>
                        </div>
                        <div id="traffic-chart-legend" class="rounded-legend legend-vertical legend-bottom-left pt-4"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Recent Tickets</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th> Assignee </th>
                                        <th> Subject </th>
                                        <th> Status </th>
                                        <th> Last Update </th>
                                        <th> Tracking ID </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <img src="{{ asset('dist/assets/images/faces/face1.jpg') }}" class="me-2"
                                                alt="image"> David Grey
                                        </td>
                                        <td> Fund is not recieved </td>
                                        <td>
                                            <label class="badge badge-gradient-success">DONE</label>
                                        </td>
                                        <td> Dec 5, 2017 </td>
                                        <td> WD-12345 </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="{{ asset('dist/assets/images/faces/face2.jpg') }}" class="me-2"
                                                alt="image"> Stella Johnson
                                        </td>
                                        <td> High loading time </td>
                                        <td>
                                            <label class="badge badge-gradient-warning">PROGRESS</label>
                                        </td>
                                        <td> Dec 12, 2017 </td>
                                        <td> WD-12346 </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="{{ asset('dist/assets/images/faces/face3.jpg') }}" class="me-2"
                                                alt="image"> Marina Michel
                                        </td>
                                        <td> Website down for one week </td>
                                        <td>
                                            <label class="badge badge-gradient-info">ON HOLD</label>
                                        </td>
                                        <td> Dec 16, 2017 </td>
                                        <td> WD-12347 </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <img src="{{ asset('dist/assets/images/faces/face4.jpg') }}" class="me-2"
                                                alt="image"> John Doe
                                        </td>
                                        <td> Loosing control on server </td>
                                        <td>
                                            <label class="badge badge-gradient-danger">REJECTED</label>
                                        </td>
                                        <td> Dec 3, 2017 </td>
                                        <td> WD-12348 </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
