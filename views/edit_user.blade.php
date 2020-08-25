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
                            <span>Edit User</span>
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
                            <span class="caption-subject bold uppercase"> Edit User </span>
                            {{--<button type="button" class="btn green positive btn-circle" id="reset_password_button"> <i class="fa fa-cog"></i> Invite to Set Password </button>--}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="portlet-body no-padding">
                <form id="user_form" action="" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
                    <input type="hidden" name="action" id="action" value="update">

                    <div class="alert alert-success" id="success_message" style="display:none"></div>
                    <div class="alert alert-danger" id="error_message" style="display: none"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name<span class="text-danger">*</span></label>
                                <input  tabindex="1" autofocus type="text" class="form-control autofocus" name="first_name" id="first_name" value="{{$user->first_name}}">
                            </div>

                            <div class="form-group">
                                <label>Email<span class="text-danger">*</span></label>
                                <input  tabindex="4" type="text" class="form-control" name="email" id="email" value="{{$user->email}}">
                            </div>

                            <div class="row custom-row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Role<span class="text-danger">*</span></label>
                                        <select  tabindex="6" class="form-control" name="role_id" id="role_id">
                                            @foreach($roles as $role)
                                                @if($role->id !=1 && $role->id < 4)
                                                    <option value="{{$role->id}}" @if($user->role_id==$role->id) selected @endif>{{$role->name}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- <div class="col-md-6" id="application_list" @if($user->role_id==1) style="display:none" @endif>
                                   <div class="form-group">
                                       <label>Application<span class="text-danger">*</span></label>
                                       <select  tabindex="7" class="form-control" name="application_id" id="application_id">
                                           <option value="">Select Application</option>
                                           @foreach($applications as $application)
                                           <option value="{{$application->application_id}}" @if($user->application_id==$application->application_id) selected @endif>{{$application->name}}</option>
                                           @endforeach
                                       </select>
                                   </div>
                                </div>--}}

                            </div>

                             <div class="row custom-row">
                                 <div class="col-md-6">
                                     <div class="form-group">
                                         <label>User Name</label>
                                         <input  tabindex="7" type="text" class="form-control" name="user_name" id="user_name" value="{{$user->login}}">
                                     </div>
                                 </div>
                                  <div class="col-md-6">
                                     <div class="form-group">
                                         <label>Password</label>
                                         <input  tabindex="8" type="password" class="form-control" name="password" id="password" value="">
                                     </div>
                                  </div>
                                 {{--<div class="col-md-4">
                                    <div class="form-group">
                                        <label>Confirm Password</label>
                                        <input  tabindex="9" type="password" class="form-control" name="confirm_password" id="confirm_password" value="">
                                    </div>
                                 </div>--}}
                             </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>MI</label>
                                        <input  tabindex="2" type="text" id="mi" name="mi" class="form-control" value="{{$user->mi}}">
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label>Last Name<span class="text-danger">*</span></label>
                                        <input  tabindex="3" type="text" class="form-control" name="last_name" id="last_name" value="{{$user->last_name}}" >
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input  tabindex="5" type="text" class="form-control" name="phone" id="phone" value="{{$user->telephone}}">
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
                                        @if($image != '')
                                            <img class="src-img img-box-normal" src="{{ asset( $image )}}">
                                        @else
                                            <img class="src-img img-box-normal" src="{{asset('assets/images/items/noimage.jpg')}}">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-20" id="users-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="portlet light bordered">
                                        <div class="custom-row row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Employee ID</label>
                                                    <input  tabindex="10" type="text" id="employee_no" name="employee_no" class="form-control" value="{{$user->employee_no}}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row custom-row">
                                                    <div class="col-xs-6">
                                                        <lavel class="checkbox mt-32">
                                                            &nbsp; &nbsp;  <input type="checkbox" name="is_active" id="is_active" value="1" @if($user->status=='active') checked @endif> Active
                                                        </lavel>
                                                    </div>
                                                    <div class="col-xs-6">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="custom-row row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label>Company</label>
                                                    <select tabindex="11" name="company_id" id="user_company_id" class="form-control">
                                                        <option value="" >Select Company </option>
                                                        @foreach($companies as $company)
                                                            <option value="{{$company->id}}" @if($company->id==$user->company_id) selected @endif>{{$company->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            {{--<div class="col-md-6">
                                                <div class="form-group mb-0">
                                                    <label>User Role</label>
                                                    <select tabindex="12" name="sub_role_id" id="sub_role_id" class="form-control">
                                                        @foreach($sub_roles as $s_role)
                                                            @if($role->name != 'Super Admin' && $role->name != 'Application Admin' && $role->name != 'User')
                                                                <option value="{{$s_role->id}}" @if($user->sub_role_id==$s_role->id) selected @endif>{{$s_role->name}}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>--}}
                                        </div>

                                    </div>
                                </div>

                                {{--<div class="col-md-6">
                                    <div class="portlet light bordered badge-section">
                                        <div class="custom-row row">
                                            <div class="col-md-4">
                                                <button type="button" class="mt-2 btn-block btn btn-sm btn-success" id="add_badge_button"> <i class="icon-plus"></i> Add Badge</button>
                                                <button type="button" class="mt-2 btn-block btn btn-sm btn-danger" id="delete_badge_button"> <i class="icon-trash"></i> Delete Badge</button>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <input type="button" id="" name="" class="form-control btn btn-success input-sm" value="Good">
                                                    <input type="hidden" name="badge_id" id="badge_id" value="@if(count($user->badges)!=0){{$user->badges[0]->id}}@endif">
                                                    <div class="white-box badge_list">
                                                        @foreach($user->badges as $key=>$badge)
                                                            <span class="badge_item @if($key==0) active @endif" data-id="{{$badge->id}}" id="badge_{{$badge->id}}">{{$badge->badge_no}}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>--}}


                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="caption font-dark">
                                <span class="caption-subject bold uppercase"> Items checkout list </span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover dataTable">
                                    <thead>
                                    <tr>
                                        <th>Tag</th>
                                        <th>Serial</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Item type</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($user->check_outes as $c_outs)
                                        <tr>
                                            <td>{{$c_outs->stock_no}}</td>
                                            <td>{{$c_outs->serial_number}}</td>
                                            <td>{{$c_outs->catalog_item_name}}</td>
                                            <td>{{$c_outs->quantity}}</td>
                                            <td>{{$c_outs->item_type}}</td>
                                            <td>{{$c_outs->status_name}}</td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-12 text-right fixed_actions">
                            <div class="circle-left xs-text-center">
                                @if(Session::get('user_id') != $user->id)
                                @endif
                                <button type="submit" name="create" role="button"  class="btn green mt-ladda-btn ladda-button btn-circle"> Update User</i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
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
                    <input type="text" class="form-control" name="new_badge" id="new_badge">

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
            display_user_info('{{$user->role_id}}');

            $('#user_form').data('serialize',$('#user_form').serialize()); // On load save form current state
           /* $(document).on("change", "#role_id", function(){
                var role = $(this).val();
                display_user_info(role);
            });*/
        })
    </script>
@stop

@section('custom-style')
@stop
@section('custom-scripts')

@stop
