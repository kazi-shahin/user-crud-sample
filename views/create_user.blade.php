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
                            <a href="{{url('/user')}}" class="ajax_item" data-name="/user" data-item="20">Users</a>
                            <i class="fa fa-circle"></i>
                        </li>
                        <li>
                            <span>Add User</span>
                        </li>
                    </ul>
                </div>

                <div class="col-md-8 col-sm-6 col-xs-12">

                </div>
            </div>
        </div>

        <div class="portlet light no-padding bordered mt-10">
            <div class="table-header mb-15">
                <div class="row">
                    <div class="col-md-6">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase"> Create User </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="portlet-body no-padding">
                <form id="user_form" action="" method="post" enctype="maltipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="action" id="action" value="store">

                    <div class="alert alert-success" id="success_message" style="display:none"></div>
                    <div class="alert alert-danger" id="error_message" style="display: none"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name<span class="text-danger">*</span></label>
                                <input tabindex="1" autofocus type="text" class="form-control autofocus" name="first_name" id="first_name">
                            </div>
                            <div class="form-group">
                                <label>Email<span class="text-danger">*</span></label>
                                <input  tabindex="4" type="text" class="form-control" name="email" id="email">
                            </div>

                            <div class="form-group">
                                <label>Role<span class="text-danger">*</span></label>
                                <select  tabindex="6" class="form-control" name="role_id" id="role_id">
                                    @foreach($roles as $role)
                                            <option value="{{$role->id}}">{{$role->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row custom-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>User Name</label>
                                        <input autocomplete="new-user"  tabindex="7" type="text" class="form-control" name="user_name" id="user_name" value="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input autocomplete="new-password" tabindex="8" type="password" class="form-control" name="password" id="password" value="">
                                    </div>
                                </div>
                              {{--  <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Confirm Password</label>
                                        <input  tabindex="8" type="password" class="form-control" name="confirm_password" id="confirm_password" value="">
                                    </div>
                                </div>--}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>MI</label>
                                        <input tabindex="2" type="text" id="mi" name="mi" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label>Last Name<span class="text-danger">*</span></label>
                                        <input  tabindex="3" type="text" class="form-control" name="last_name" id="last_name">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input  tabindex="5" type="text" class="form-control" name="phone" id="phone">
                            </div>

                            <div class="form-group mb-0">
                                <label>Add Picture</label><br>
                                <div class="attachment-box">
                                    <div class="attachment-btn">
                                        <input id="my-file-selector" type="file" class="hidden" name="image" accept="image/*">
                                        <label for="my-file-selector" class="btn btn-default btn-sm">
                                            <i class="fa fa-upload"></i> Browse
                                        </label>
                                        <button type="button" class="btn btn-danger btn-sm" id="img_remove">
                                            <i class="fa fa-trash"></i> Remove
                                        </button>
                                    </div>

                                    <div class="attachment-file-view">
                                        <img class="src-img img-box-normal" src="{{asset('assets/images/items/noimage.jpg')}}">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-md-12 mt-20" id="users-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="portlet light bordered">
                                        <div class="form-group">
                                            <label>Employee ID</label>
                                            <input tabindex="9" type="text" id="employee_no" name="employee_no" class="form-control" value="">
                                        </div>

                                        <div class="custom-row row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label>Company</label>
                                                    <select tabindex="10" name="company_id" id="user_company_id" class="form-control">
                                                        <option value="" >Select Company </option>
                                                        @foreach($companies as $company)
                                                            <option value="{{$company->id}}">{{$company->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label>User Role</label>
                                                    <select tabindex=11 name="sub_role_id" id="sub_role_id" class="form-control">
                                                        @foreach($roles as $role)
                                                            @if($role->name != 'Super Admin' && $role->name != 'Application Admin' && $role->name != 'User')
                                                                <option value="{{$role->id}}">{{$role->name}}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="portlet light bordered badge-section">
                                        <div class="row custom-row">
                                            <div class="col-md-6">
                                                <lavel class="checkbox mt-20" style="margin-left: 30px">
                                                    <input type="checkbox" name="is_active" id="is_active" value="1" checked> Active
                                                </lavel>
                                            </div>
                                            <div class="col-md-6">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Add Badge</label>
                                            <input tabindex="12" type="text" id="badge_no" name="badge_no" class="form-control" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 text-right fixed_actions">
                            <div class="circle-left xs-text-center">
                                <button type="submit" name="create" role="button"  class="btn green mt-ladda-btn ladda-button btn-circle"> Create User
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_badge_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Add Badge</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="alert alert-success" id="badge_success_message" style="display:none"></div>
                <div class="alert alert-danger" id="badge_error_message" style="display: none"></div>

                <div class="modal-body">
                    <input type="text"  name="new_badge" class="form-control" id="new_badge">

                    <div class="form-group mb-0 text-right mt-10">
                        <button type="button" class="btn btn-primary" id="add_badge">Add</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('#user_form').data('serialize',$('#user_form').serialize()); // On load save form current state
           /* $(document).on("change", "#role_id", function(){
                var role = $(this).val();
                if(role == 3){
                    $("#users-info").show();
                }else{
                    $("#users-info").hide();
                }
                // display_user_info(role);
            });*/
        });
    </script>
@stop

@section('custom-style')
@stop
@section('custom-scripts')

@stop
