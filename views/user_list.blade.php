@extends('layout')
@section('content')
    <div class="page-content">
        <div class="page-bar">
            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <ul class="page-breadcrumb">
                        <li>
                            <a href="{{url('/')}}" class="ajax_item" data-name="/" data-item="1">Home</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Users</span>
                        </li>
                    </ul>
                </div>

                <div class="col-md-8 col-sm-6 col-xs-12">

                </div>
            </div>
        </div>
        <!-- END PAGE TITLE-->

        <!-- END PAGE HEADER-->
        <div class="portlet light no-padding bordered mt-10">
            <div class="table-header mb-15">
                <div class="row">
                    <div class="col-md-6">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> Users </span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="action-header">
                            <div class="circle-left xs-text-center">
                                <a href="{{url('/create_user')}}" name="create" role="button"  class="btn green mt-ladda-btn ladda-button  btn-circle ajax_item" data-name="/create_user" data-item="20"> Add New
                                    <i class="fa fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="portlet-body no-padding">
                <table class="table table-bordered table-hover mt-10" id="app-list-table">
                    <thead>
                    <tr class="btn-info">
                        <th class="w-5 text-center"></th>
                        <th class="w-31"> Name </th>
                        <th class="w-16"> User Name </th>
                        <th class="w-16"> Email </th>
                        <th class="w-16"> Role </th>
                        <th class="w-31"> Last Login </th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php

                    $count = 1;
                    foreach($users as $user){
                    ?>
                    <tr>
                        <td class="text-center">
                            <span class="hidden"> {{$user->id}} </span>
                            <a href="{{url('/edit_user',$user->id)}}" class="btn reg_action_btn btn-primary btn-circle btn-xs ajax_item" data-name="/edit_user/{{$user->id}}" data-item="20" title="Edit {{$user->id}}">
                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            </a>

                        </td>
                        <td>
                            {{ $user->first_name }}
                            @if($user->mi !='')
                                {{" ".$user->mi}}
                            @endif
                            {{" ".$user->last_name}}
                        </td>
                        <td> {{ $user->login }} </td>
                        <td> {{ $user->email }} </td>
                        <td>
                            @if($user->userRole)
                                {{ $user->userRole->name }}
                            @endif
                        </td>
                        <td> @if($user->last_login!=''){{date("M d, Y h:i:s ", strtotime($user->last_login))}} @endif
                        </td>

                    </tr>
                    <?php
                    $count++;
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('#app-list-table').DataTable({
                "ordering": true,
                "order": [[1, 'asc']],
                "paging": false,
                "info": false,
            });
        })
    </script>
@stop

@section('custom-style')
@stop
@section('custom-scripts')

@stop
