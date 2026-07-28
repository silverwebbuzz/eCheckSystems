@extends('layouts/layoutMaster')

@section('title', 'View Suggestion')


@section('content')
    <div class="card">
        <div class="card-header text-end">
            {{-- <h5>Suggestion</h5> --}}
            <div class="text-end">
                <a href="{{ route('admin.suggestions.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        <div class="card-body mt-5">
            <div class="row">
                <div class="">
                    <table class="table table-borderless">
                        <tr>
                            <td width="126.94px"><b>User</b></td>
                            <td>: &nbsp; {{ $suggestion->user->FirstName }} {{ $suggestion->user->LastName }}</td>
                        </tr>
                        <tr>
                            <td width="126.94px"><b>Email</b></td>
                            <td>: &nbsp; {{ $suggestion->user->Email }}</td>
                        </tr>
                        <tr>
                            <td width="126.94px"><b>Section</b></td>
                            <td>: &nbsp; {{ $suggestion->section }}</td>
                        </tr>
                        {{-- <th>Description </th>
                <td>: {!! $suggestion->description !!}</td> --}}
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <table class="table table-borderless">
                        <tr>
                            <td width="126.94px" style="vertical-align: top"><b>Description</b></td>
                            <td> :  <div style="margin-left:13px; margin-top:-42px;">{!! '&nbsp;'.$suggestion->description !!}</div></td>
                        </tr>
                    </table>
                </div   >
            </div>
        </div>
    </div>
@endsection
