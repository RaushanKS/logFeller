@extends('layouts.app')

@section('content')
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin Profile /</span> Profile
    </h4>

    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                {{-- <div class="user-profile-header-banner">
                    <img src="../../assets/img/pages/profile-banner.png" alt="Banner image" class="rounded-top">
                </div> --}}
                <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                    <?php
                        if (!empty($user->image)) {
                            $img = $user->image;
                        } else {
                            $img = 'assets/img/avatars/14.png';
                            
                        }
                    ?>
                    <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                        <img src="{{ asset('/') }}{{ $img }}" alt="user image"
                            class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img">
                    </div>
                    <div class="flex-grow-1 mt-3 mt-sm-5">
                        <div
                            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                            <div class="user-profile-info">
                                <h4>{{ $user->name }}</h4>
                                {{-- <ul
                                    class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                    <li class="list-inline-item d-flex gap-1">
                                        <i class='ti ti-color-swatch'></i> UX Designer
                                    </li>
                                    <li class="list-inline-item d-flex gap-1">
                                        <i class='ti ti-map-pin'></i> Vatican City
                                    </li>
                                    <li class="list-inline-item d-flex gap-1">
                                        <i class='ti ti-calendar'></i> Joined April 2021
                                    </li>
                                </ul> --}}
                            </div>
                            {{-- <a href="javascript:void(0)" class="btn btn-primary">
                                <i class='ti ti-check me-1'></i>Connected
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ Header -->

    <!-- User Profile Content -->
    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-5">
            <!-- About User -->
            <div class="card mb-4">
                <div class="card-body">
                    <small class="card-text text-uppercase">About</small>
                    <ul class="list-unstyled mb-4 mt-3">
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-user text-heading"></i><span
                                class="fw-medium mx-2 text-heading">Full Name:</span> <span>{{ $user->name }}</span></li>
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-check text-heading"></i><span
                                class="fw-medium mx-2 text-heading">Status:</span> <span>Active</span></li>
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-file-description text-heading"></i><span
                                class="fw-medium mx-2 text-heading">Languages:</span> <span>English</span></li>
                    </ul>
                    <small class="card-text text-uppercase">Contacts</small>
                    <ul class="list-unstyled mb-4 mt-3">
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-phone-call"></i><span
                                class="fw-medium mx-2 text-heading">Contact:</span> <span>{{ $user->phone }}</span></li>
                        <li class="d-flex align-items-center mb-3"><i class="ti ti-mail"></i><span
                                class="fw-medium mx-2 text-heading">Email:</span> <span>{{ $user->email }}</span></li>
                    </ul>
                </div>
            </div>
            <!--/ About User -->

        </div>
        <div class="col-xl-8 col-lg-5 col-md-5">
            <!-- About User -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-2 mt-5">Update Profile Info </h4> <hr>
                    <form action="javascript:void(0);" id="personal-info-admin" method="POST" class="mb-5">
                        @csrf
                        <div class="mb-3">
                            <label for="formrow-email-input" class="col-form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="formrow-email-input"
                                value="{{ $user->email }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="formrow-firstname-input" class="col-form-label">Name</label>
                            <input type="text" name="name" class="form-control"
                                id="formrow-firstname-input" value="{{ $user->name }}"
                                placeholder="Enter Your First Name" required>
                        </div>

                        <div class="mb-3">
                            <label for="formrow-phone-input" class="col-form-label">Phone</label>
                            <input type="text" class="form-control" id="formrow-phone-input" name="contactNo"
                                value="{{ $user->phone }}" pattern="\d{10}"
                                title="Error: 10 digits are required." maxlength="13"
                                onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode &gt;= 48 && event.charCode &lt;= 57">
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary w-md"
                                onclick="profileinfoformSubmit(this);"
                                data-action="{{ url('profile-info-update') }}/{{ $user->id }}"
                                data-id="personal-info-admin" style="float: right">Update Profile</button>
                        </div>
                    </form>

                    <h4 class="card-title mb-2 mt-5">Update Profile Image </h4>
                    <hr>

                    <form action="javascript:void(0);" id="admin-profile-update" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="formFile" class="col-form-label">Profile Image</label>
                            <input class="form-control" type="file" id="formFile" name="profileImage"
                                accept=".jpg, .jpeg, .png">
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary w-md" onclick="formImageSubmit(this);"
                                id="post-form"
                                data-action="{{ url('profile-image-update') }}/{{ $user->id }}"
                                data-id="admin-profile-update"  style="float: right">Update Profile Image</button>
                        </div>
                    </form>
                </div>
            </div>
            <!--/ About User -->

        </div>

    </div>
@endsection
