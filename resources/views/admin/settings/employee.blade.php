@extends('layouts.app')
@section('title', __("messages.setting-label", ["name" => __("user")]))
@section('content')
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-end align-items-center w-100">
                        <button class="btn btn-success" type="button"  data-toggle="modal" data-target="#TambahData" id="modal-button"><i class="fas fa-plus m-1"></i>{{ __("add data") }}</button>
                    </div>
                </div>


                <!-- Modal -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="TambahDataModalLabel">{{ __("add data") }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" onclick="clear()" >&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Tambah tab untuk pemilihan form -->
                            <ul class="nav nav-tabs" id="form-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="user-tab" data-toggle="tab" href="#user-form" role="tab">{{ __("User") }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="role-tab" data-toggle="tab" href="#role-form" role="tab">{{ __("Role") }}</a>
                                </li>
                            </ul>
                            
                            <div class="tab-content mt-3">
                                <!-- Form User -->
                                <div class="tab-pane fade show active" id="user-form" role="tabpanel">
                                    <div class="form-group mb-3">
                                        <label for="name">{{ __("name") }}</label>
                                        <input type="text" class="form-control" id="name" autocomplete="off">
                                        <input type="hidden" name="id" id="id">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="phone_number">{{ __("username") }}</label>
                                        <input type="text" class="form-control" id="username" autocomplete="off">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="password">{{ __("password") }}</label>
                                        <input type="password" class="form-control" id="password"></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="role">{{ __("role") }}</label>
                                        <select class="form-control" id="role">
                                            <option selected value="-- {{ __('role') }} --">-- {{ __("role") }} --</option>
                                            @foreach($roles as $role)
                                                <option value="{{$role->id}}">{{$role->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Form Role -->
                                <div class="tab-pane fade" id="role-form" role="tabpanel">
                                    <div class="form-group mb-3">
                                        <label for="role_name">{{ __("role name") }}</label>
                                        <input type="text" class="form-control" id="role_name" autocomplete="off">
                                        <input type="hidden" name="role_id" id="role_id">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="kembali">{{ __("back") }}</button>
                            <button type="button" class="btn btn-success" id="simpan">{{ __("save") }}</button>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-tabel" width="100%"  class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="4%">{{ __("no") }}</th>
                                    <th class="border-bottom-0">{{ __("name") }}</th>
                                    <th class="border-bottom-0">{{ __("username") }}</th>
                                    <th class="border-bottom-0">{{ __("role") }}</th>
                                    <th class="border-bottom-0" width="1%">{{ __("action") }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<x-data-table/>
<script>
    function isi(){
        $('#data-tabel').DataTable({
            responsive: true, lengthChange: true, autoWidth: false,
            processing:true,
            serverSide:true,
            ajax:`{{route('settings.employee.list')}}`,
            columns:[
                {
                    "data":null,"sortable":false,
                    render:function(data,type,row,meta){
                        return meta.row + meta.settings._iDisplayStart+1;
                    }
                },
                {
                    data:'name',
                    name:'name'
                },
                {
                    data:'username',
                    name:'username',
                },
                {
                    data:'role_name',
                    name:'role_name',
                },
                {
                    data:'tindakan',
                    name:'tindakan'
                }
            ]
        }).buttons().container();
    }

    function simpan(){
        // Cek aktif tab mana
        const activeTab = $('#form-tabs .nav-link.active').attr('id');
        
        if (activeTab === 'user-tab') {
            // Simpan user
            $.ajax({
                url:`{{route('settings.employee.save')}}`,
                type:"post",
                data:{
                    name:$("#name").val(),
                    username:$("#username").val(),
                    password:$("#password").val(),
                    role_id:$("#role").val(),
                    "_token":"{{csrf_token()}}"
                },
                success:function(res){
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#kembali').click();
                    $("#name").val(null);
                    $("#username").val(null);
                    $("#password").val(null);
                    $("#role").val("-- {{__('role')}} --");
                    $('#data-tabel').DataTable().ajax.reload();
                },
                error:function(err){
                    console.log(err);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: err.responseJSON ? err.responseJSON.message : "Error occurred",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
            });
        } else if (activeTab === 'role-tab') {
            // Simpan role
            $.ajax({
                url:`{{route('settings.role.save')}}`,
                type:"post",
                data:{
                    name:$("#role_name").val(),
                    "_token":"{{csrf_token()}}"
                },
                success:function(res){
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#kembali').click();
                    $("#role_name").val(null);
                    // Reload halaman untuk memperbarui dropdown role
                    location.reload();
                },
                error:function(err){
                    console.log(err);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: err.responseJSON ? err.responseJSON.message : "Error occurred",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
            });
        }
    }


    function ubah(){
        // Cek aktif tab mana
        const activeTab = $('#form-tabs .nav-link.active').attr('id');
        
        if (activeTab === 'user-tab') {
            // Update user
            $.ajax({
                url:`{{route('settings.employee.update')}}`,
                type:"put",
                data:{
                    id:$("#id").val(),
                    name:$("#name").val(),
                    username:$("#username").val(),
                    password:$("#password").val(),
                    role_id:$("#role").val(),
                    "_token":"{{csrf_token()}}"
                },
                success:function(res){
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#kembali').click();
                    $("#name").val(null);
                    $("#username").val(null);
                    $("#password").val(null);
                    $("#role").val("-- {{ __('role') }} --");
                    $('#data-tabel').DataTable().ajax.reload();
                    $('#simpan').text("{{__('save')}}");
                },
                error:function(err){
                    console.log(err.responseJSON);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: err.responseJSON ? err.responseJSON.message : "Error occurred",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
            });
        } else if (activeTab === 'role-tab') {
            // Update role
            $.ajax({
                url:`{{route('settings.role.update')}}`,
                type:"put",
                data:{
                    id:$("#role_id").val(),
                    name:$("#role_name").val(),
                    "_token":"{{csrf_token()}}"
                },
                success:function(res){
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#kembali').click();
                    $("#role_name").val(null);
                    // Reload halaman untuk memperbarui dropdown role
                    location.reload();
                },
                error:function(err){
                    console.log(err.responseJSON);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: err.responseJSON ? err.responseJSON.message : "Error occurred",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
            });
        }
    }

    $(document).ready(function(){
        isi();

        $('#simpan').on('click',function(){
            if($(this).text() === "{{__('update')}}"){
                ubah();
            }else{
                simpan();
            }
        });

        $("#modal-button").on("click",function(){
            // Reset form
            $("#name").val(null);
            $("#username").val(null);
            $("#password").val(null);
            $("#role").val("-- {{ __('role') }} --");
            $("#role_name").val(null);
            $("#role_id").val(null);
            
            // Aktifkan tab user secara default
            $('#user-tab').tab('show');
            
            $("#simpan").text("{{__('save')}}");
            $("#TambahDataModalLabel").text("{{__('add data')}}");
        });
        
        // Update judul modal saat berganti tab
        $('#form-tabs a').on('shown.bs.tab', function (e) {
            const targetId = $(e.target).attr('id');
            if (targetId === 'user-tab') {
                $("#TambahDataModalLabel").text("{{__('add user')}}");
            } else if (targetId === 'role-tab') {
                $("#TambahDataModalLabel").text("{{__('add role')}}");
            }
        });
    });



    $(document).on("click",".ubah",function(){
        let id = $(this).attr('id');
        $("#modal-button").click();
        $("#simpan").text("{{__('update')}}");
        $("#TambahDataModalLabel").text("Ubah Profile Staff");
        $.ajax({
            url:"{{route('settings.employee.detail')}}",
            type:"post",
            data:{
                id:id,
                "_token":"{{csrf_token()}}"
            },
            success:function({data}){
                $("#id").val(data.id);
                $("#name").val(data.name);
                $("#username").val(data.username);
                $("#role").val(data.role_id);
            }
        });

    });

    $(document).on("click",".hapus",function(){
        let id = $(this).attr('id');
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success m-1",
                cancelButton: "btn btn-danger m-1"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "{{__('you are sure')}} ?",
            text: "{{__('this data will be deleted')}}",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "{{__('yes, delete')}}",
            cancelButtonText: "{{__('no, cancel')}}!",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"{{route('settings.employee.delete')}}",
                    type:"delete",
                    data:{
                        id:id,
                        "_token":"{{csrf_token()}}"
                    },
                    success:function(res){
                        Swal.fire({
                                position: "center",
                                icon: "success",
                                title: res.message,
                                showConfirmButton: false,
                                timer: 1500
                        });
                        $('#data-tabel').DataTable().ajax.reload();
                    }
                });
            }
        });


    });
</script>
@endsection