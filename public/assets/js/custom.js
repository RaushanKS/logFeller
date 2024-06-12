// Profile update Section

const validateEmail = (email) => {
    return String(email)
        .toLowerCase()
        .match(
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        );
};

function profileinfoformSubmit(e) {
    let formId = $(e).attr("data-id");
    var elements = document.getElementById(formId).elements;
    var obj = {};
    for (var i = 0; i < elements.length; i++) {
        var item = elements.item(i);

        if (item.value == "" && item.id != "undefined" && item.id != "") {
            let dataIDGet = $("#" + item.id).attr("data-id");
            let dataRequiredGet = $("#" + item.id).attr("data-name");
            if (
                dataRequiredGet === "yes" &&
                dataIDGet != "" &&
                dataIDGet != "undefined"
            ) {
                $("#" + dataIDGet).show();
                $("#" + item.id).focus();
                return false;
            } else {
                $("#" + dataIDGet).hide();
            }
        } else {
            if (
                item.value != "" &&
                item.name != "_token" &&
                item.id != "undefined" &&
                item.id != ""
            ) {
                let dataIDGet = $("#" + item.id).attr("data-id");
                if (dataIDGet != "" && dataIDGet != "undefined") {
                    if (item.id == "email") {
                        if (!validateEmail(item.value)) {
                            $("#" + dataIDGet).text("Please enter valid email");
                            $("#" + dataIDGet).show();
                            $("#" + item.id).focus();
                            return false;
                        } else {
                            $("#" + dataIDGet).text("This is required field");
                            $("#" + dataIDGet).hide();
                        }
                    }
                    $("#" + dataIDGet).hide();
                }
            }
        }
        obj[item.name] = item.value;
    }

    let action = $(e).attr("data-action");
    $.ajax({
        type: "POST",
        url: action,
        data: obj,
        success: function (response) {
            if (response.success == true) {
                let html =
                    '<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="mdi mdi-check-all me-2"></i>'
                    + response.message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                // '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                // response.message +
                // "</div></div>";
                $(".messageShowAlert").append(html);
                setTimeout(function () {
                    $(".toast-autohide").remove();
                }, 3000);
                if (response.redirectUrl) {
                    $(location).attr("href", response.redirectUrl);
                }
            } else {
                let html =
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="mdi mdi-check-all me-2"></i>'
                    + response.message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                // '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                // response.message +
                // "</div></div>";
                $(".messageShowAlert").append(html);
                setTimeout(function () {
                    $(".toast-autohide").remove();
                }, 3000);
                $(location).attr("href", window.location);
            }
        },
    });
}

function formImageSubmit(e) {
    let formId = $(e).attr("data-id");
    var file = $("input[type=file]")[0].files[0];
    let action = $(e).attr("data-action");
    var form_data = new FormData();
    form_data.append("file", file);
    form_data.append("_token", $('meta[name="csrf-token"]').attr("content"));
    $.ajax({
        type: "POST",
        url: action,
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        success: function (response) {
            if (response.success == true) {
                console.log(`{{ asset('/') }}`);
                $(".userProfile").attr("src", baseUrl + "/" + response.file);
                let html =
                    '<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="mdi mdi-check-all me-2"></i>'
                    + response.message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                $(".messageShowAlert").append(html);
                setTimeout(function () {
                    $(".toast-autohide").remove();
                }, 3000);
                $(location).attr("href", window.location);
            } else {
                let html =
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="mdi mdi-check-all me-2"></i>'
                    + response.message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                $(".messageShowAlert").append(html);
                setTimeout(function () {
                    $(".toast-autohide").remove();
                }, 3000);
                $(location).attr("href", window.location);
            }
        },
    });
}

//Users table code starts  

if ($("#userTable").length > 0) {
    $("#userTable").DataTable({
        dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end me-2"B>><"user_status mt-50 width-200"><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

        // buttons: [
        //     {
        //         className: "btn btn-danger me-2 DeleteALL",
        //         text: "Delete",
        //     },
        //     {
        //         className: "btn btn-primary onlineAll",
        //         text: "Active",
        //     },
        //     {
        //         className: "btn btn-warning offlineAll",
        //         text: "InActive",
        //     },
        //     {
        //         extend: "collection",
        //         className: "btn btn-outline-info dropdown-toggle me-2",
        //         text: "Export",
        //         buttons: [
        //             {
        //                 extend: "print",
        //                 text: "Print",
        //                 className: "dropdown-item",
        //                 exportOptions: {
        //                     columns: [1, 2, 3],
        //                 },
        //             },
        //             {
        //                 extend: "csv",
        //                 text: "Csv",
        //                 className: "dropdown-item",
        //                 exportOptions: {
        //                     columns: [1, 2, 3],
        //                 },
        //             },
        //             {
        //                 extend: "excel",
        //                 text: "Excel",
        //                 className: "dropdown-item",
        //                 exportOptions: {
        //                     columns: [1, 2, 3],
        //                 },
        //             },
        //             {
        //                 extend: "pdf",
        //                 text: "Pdf",
        //                 className: "dropdown-item",
        //                 exportOptions: {
        //                     columns: [1, 2, 3],
        //                 },
        //             },
        //             {
        //                 extend: "copy",
        //                 text: "Copy",
        //                 className: "dropdown-item",
        //                 exportOptions: {
        //                     columns: [1, 2, 3],
        //                 },
        //             },
        //         ],
        //         init: function (api, node, config) {
        //             $(node).removeClass("btn-secondary");
        //             $(node).parent().removeClass("btn-group");
        //             setTimeout(function () {
        //                 $(node)
        //                     .closest(".dt-buttons")
        //                     .removeClass("btn-group")
        //                     .addClass("d-inline-flex");
        //             }, 50);
        //         },
        //     },
        // ],
        initComplete: function () {
            this.api()
                .columns(6)
                .every(function () {
                    var column = this;
                    var select = $(
                        // '<select id="orderStatus" class="select2 form-select text-capitalize mb-3" style="margin-top:0.9em; margin-right:0.9em"><option value=""> Select Status </option><option value="1">Active</option><option value="0">Incative</option></select>'
                    )
                        .appendTo(".user_status")
                        .on("change", function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column
                                .search(val ? "" + val + "" : "", true, false)
                                .draw();
                        });
                });
        },
        retrieve: true,
        paging: true,
        processing: true,
        serverSide: true,
        ajax: baseUrl + "/users/getUsers/",
        pageLength: 10,
        language: {
            searchPlaceholder: "Search Name/Email/Phone",
        },
        columns: [
            {
                data: "id",
            },
            {
                data: "name",
            },
            {
                data: "email",
            },
            {
                data: "phone",
            },
            {
                data: "date",
            },
            {
                data: "status",
            },
        ],
        aoColumnDefs: [
            {
                targets: 0,
                orderable: false,
                checkboxes: {
                    selectRow: true,
                },
            },

            {
                aTargets: [1],
                mData: "id",
                mRender: function (data, type, row, meta) {

                    if (row.parent_id !== 0) {
                        return "<p class='childNode'>" + row.name + "</p>";
                    } else {
                        return "<p>" + row.name + "</p>";
                    }

                },
            },
            {
                aTargets: [2],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.parent_id !== 0) {
                        return "<p class='childNode'>" + row.email + "</p>";
                    } else {
                        return "<p>" + row.email + "</p>";
                    }
                },
            },
            {
                aTargets: [3],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.parent_id !== 0) {
                        return "<p class='childNode'>" + row.phone + "</p>";
                    } else {
                        return "<p>" + row.phone + "</p>";
                    }
                },
            },
            {
                aTargets: [4],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.parent_id !== 0) {
                        return "<p class='childNode'>" + row.date + "</p>";
                    } else {
                        return "<p>" + row.date + "</p>";
                    }
                },
            },
            {
                aTargets: [5],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    var statusText = row.status == 1 ? 'Active' : 'Inactive';
                    var badgeClass = row.status == 1 ? 'bg-success bg-glow' : 'bg-danger bg-glow';
                    var html = '<a class="updateUserStatus" id="filter-' + row.id + '" filter_id="' + row.id + '" href="javascript:void(0)">';
                    html += '<span class="badge ' + badgeClass + '" status="' + statusText + '">' + statusText + '</span></a>';
                    return html;
                }
            },

            {
                targets: 6,
                orderable: false,
            },
            {
                aTargets: [6],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    // let editUrl = baseUrl + "/admin/blog-post/edit/" + row.id;
                    // let viewUrl = baseUrl + "/admin/video/view/" + row.id;
                    let deleteUrl = baseUrl + "/user/delete/" + row.id;
                    // <a class="action-class view-access editIcon" href="' +viewUrl +'" id="view_' +row.id +'"  ><i class="far fa-eye" aria-hidden="true"></i></a>
                    return (
                        // '<a class="action-class view-access editIcon" href="' +
                        // editUrl +
                        // '" id="edit_' +
                        // row.id +
                        // '"  ><i class="mdi mdi-lead-pencil" aria-hidden="true"></i></a>
                        '<a class="delete-record action-class deleteIcon" href="javascript:void(0)" data-url="' +
                        deleteUrl +
                        '" onclick="confirmation(this);"  data-type="User" id="delete_record_' +
                        row.id +
                        '"><i class="fa fa-trash" aria-hidden="true"></i></a>'
                    );
                },
            },
        ],
        select: {
            style: "multi",
        },
        order: [[1, "asc"]],
    });

    $(document).on("click", ".updateUserStatus", function () {
        var status = $(this).children("span").attr("status");
        // console.log(status);
        var filter_id = $(this).attr("filter_id");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: '/update-user-status',
            data: { status: status, filter_id: filter_id },
            success: function (resp) {
                if (resp['status'] == 1) {
                    $("#filter-" + filter_id).html("<span class='badge bg-success bg-glow' status='Active'>Active</span>")
                } else if (resp['status'] == 0) {
                    $("#filter-" + filter_id).html("<span class='badge bg-danger bg-glow' status='Inactive'>Inactive</span>")
                }
            }, error: function () {
                alert("Error")
            }
        })
    });
}


//Products
if ($("#productsTable").length > 0) {
    $("#productsTable").DataTable({
        dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"user_status mt-50 width-200"><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                className: "btn btn-danger me-2 DeleteALL",
                text: "Delete",
            },
            {
                className: "btn btn-success me-2 onlineAll",
                text: "Active",
            },
            {
                className: "btn btn-warning me-2 offlineAll",
                text: "InActive",
            },
            {
                extend: "collection",
                className: "btn btn-info dropdown-toggle me-2",
                text: "Export",
                buttons: [
                    {
                        extend: "print",
                        text: "Print",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4],
                        },
                    },
                    {
                        extend: "csv",
                        text: "Csv",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4],
                        },
                    },
                    {
                        extend: "excel",
                        text: "Excel",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4],
                        },
                    },
                    {
                        extend: "pdf",
                        text: "Pdf",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4],
                        },
                    },
                    {
                        extend: "copy",
                        text: "Copy",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4],
                        },
                    },
                ],
                init: function (api, node, config) {
                    $(node).removeClass("btn-secondary");
                    $(node).parent().removeClass("btn-group");
                    setTimeout(function () {
                        $(node)
                            .closest(".dt-buttons")
                            .removeClass("btn-group")
                            .addClass("d-inline-flex");
                    }, 50);
                },
            },
        ],
        initComplete: function () {
            this.api()
                .columns(4)
                .every(function () {
                    var column = this;
                    var select = $(
                        '<select id="orderStatus" class="form-select text-capitalize"><option value=""> Select Status </option><option value="1">Active</option><option value="0">Incative</option></select>'
                    )
                        .appendTo(".user_status")
                        .on("change", function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column
                                .search(val ? "" + val + "" : "", true, false)
                                .draw();
                        });
                });
        },
        retrieve: true,
        paging: true,
        processing: true,
        serverSide: true,
        ajax: baseUrl + "/products/getProducts",
        pageLength: 10,
        language: {
            searchPlaceholder: "Search By Product Name",
        },
        columns: [
            {
                data: "id",
            },
            {
                data: "image",
            },
            {
                data: "name",
            },
            {
                data: "price",
            },
            {
                data: "description",
            },
            {
                data: "status",
            },
        ],
        aoColumnDefs: [
            {
                targets: 0,
                orderable: false,
                checkboxes: {
                    selectRow: true,
                },
            },

            {
                aTargets: [1],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.image !== undefined && row.image !== null) {
                        return (
                            '<img class="round" src="' +
                            baseUrl +
                            "/" +
                            row.image +
                            '" alt="' +
                            row.name +
                            '" width="40px" height="40px" />'
                        );
                    } else {
                        return (
                            '<img class="round" src="' +
                            baseUrl +
                            '/assets/img/placeholder.png" alt="' +
                            row.name +
                            '" width="40px" height="40px" />'
                        );
                    }
                },
            },
            {
                aTargets: [2],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.parent_id !== 0) {
                        return "<p class='childNode'>" + row.name + "</p>";
                    } else {
                        return "<p>" + row.name + "</p>";
                    }
                },
            },
            {
                targets: 6,
                orderable: false,
            },
            {
                aTargets: [5],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.status == 1) {
                        return '<span class="badge bg-success bg-glow">Acive</span>';
                    } else {
                        return '<span class="badge bg-danger bg-glow">In-Active</span>';
                    }
                },
            },
            {
                aTargets: [6],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    let editUrl = baseUrl + "/product/edit/" + row.id;
                    let viewUrl = baseUrl + "/product/view/" + row.id;
                    let deleteUrl = baseUrl + "/product/delete/" + row.id;
                    // <a class="action-class view-access editIcon" href="' +viewUrl +'" id="view_' +row.id +'"  ><i class="far fa-eye" aria-hidden="true"></i></a>
                    return (
                        '<a class="action-class view-access editIcon me-2" href="' +
                        editUrl +
                        '" id="edit_' +
                        row.id +
                        '"  ><i class="far fa-edit" aria-hidden="true"></i></a><a class="delete-record action-class deleteIcon" href="javascript:void(0)" data-url="' +
                        deleteUrl +
                        '" onclick="confirmation(this);"  data-type="product" id="delete_record_' +
                        row.id +
                        '"><i class="fa fa-trash" aria-hidden="true"></i></a>'
                    );
                },
            },
        ],
        select: {
            style: "multi",
        },
        order: [[2, "asc"]],
    });

    $(".onlineAll").on("click", function (e) {
        let dt_user_table = $("#productsTable").DataTable();
        console.log(dt_user_table);
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to active",
            icon: "warning",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            // cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/products/online-all/1",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                                document.location.reload();
                            }, 3000);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                                document.location.reload();
                            }, 3000);
                        }
                    },
                });
            }
        });
    });

    $(".offlineAll").on("click", function (e) {
        let dt_user_table = $("#productsTable").DataTable();
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to inactive",
            icon: "warning",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            // cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/products/online-all/0",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        }
                    },
                });
            }
        });
    });

    $(".DeleteALL").on("click", function (e) {
        let dt_user_table = $("#productsTable").DataTable();
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        console.log(rows_selected.length);
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to delete",
            icon: "warning",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            // cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/products/delete-all",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        }
                    },
                });
            }
        });
    });
}

//delete product image
function removeServiceImage(id) {
    let imagePath = $("#" + id).data("src");
    let imageIndex = $("#" + id).data("id"); 
    let urlRequest = $("#" + id).data("url");
    let divId = $("#" + id).data("type");

    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to delete this image?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        // cancelButtonText: "No",
        confirmButtonText: "Yes",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                id: imageIndex,
                url: urlRequest,
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    id: imageIndex,
                    imagePath: imagePath,
                },
                success: function (response) {
                    if (response.status == "success") {
                        $("#" + divId).remove();
                        $(location).attr("href", window.location);
                    } else {
                        $(location).attr("href", window.location);
                    }
                },
            });
        }
    });
}

function discountType(id) {
    let discountType = $("#" + id).val();
    // console.log(discountType);
    if (discountType == "fixed") {
        $("#fixedDiscount").show();
        $("#discount_percent").val("");
        $("#percentageDiscount").hide();
    } else {
        $("#fixedDiscount").hide();
        $("#discount_amount").val("");
        $("#percentageDiscount").show();
    }
}

//Discounts
if ($("#discountsTables").length > 0) {
    $("#discountsTables").DataTable({
        dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"user_status mt-50 width-200"><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                className: "btn btn-danger me-2 DeleteALL",
                text: "Delete",
            },
            {
                className: "btn btn-success me-2 onlineAll",
                text: "Active",
            },
            {
                className: "btn btn-warning me-2 offlineAll",
                text: "In - Active",
            },
            {
                extend: "collection",
                className: "btn btn-info dropdown-toggle me-2",
                text: "Export",
                buttons: [
                    {
                        extend: "print",
                        text: "Print",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7],
                        },
                    },
                    {
                        extend: "csv",
                        text: "Csv",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7],
                        },
                    },
                    {
                        extend: "excel",
                        text: "Excel",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7],
                        },
                    },
                    {
                        extend: "pdf",
                        text: "Pdf",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7],
                        },
                    },
                    {
                        extend: "copy",
                        text: "Copy",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7],
                        },
                    },
                ],
                init: function (api, node, config) {
                    $(node).removeClass("btn-secondary");
                    $(node).parent().removeClass("btn-group");
                    setTimeout(function () {
                        $(node)
                            .closest(".dt-buttons")
                            .removeClass("btn-group")
                            .addClass("d-inline-flex");
                    }, 50);
                },
            },
        ],
        initComplete: function () {
            this.api()
                .columns(7)
                .every(function () {
                    var column = this;
                    var select = $(
                        '<select id="orderStatus" class="form-select text-capitalize"><option value=""> Select Status </option><option value="1">Active</option><option value="0">In-Active</option></select>'
                    )
                        .appendTo(".user_status")
                        .on("change", function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column
                                .search(val ? "" + val + "" : "", true, false)
                                .draw();
                        });
                });
        },
        retrieve: true,
        paging: true,
        processing: true,
        serverSide: true,
        ajax: baseUrl + "/coupons/getDiscounts",
        pageLength: 10,
        language: {
            searchPlaceholder: "Search By Name",
        },
        columns: [
            {
                data: "id",
            },
            {
                data: "name",
            },
            {
                data: "discount_amount",
            },
            {
                data: "max_discount",
            },
            {
                data: "min_order_amount",
            },
            {
                data: "start_date",
            },
            {
                data: "created_at",
            },
            {
                data: "status",
            },
        ],
        aoColumnDefs: [
            {
                targets: 0,
                orderable: false,
                checkboxes: {
                    selectRow: true,
                },
            },
            {
                aTargets: [2],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.discount_type == "fixed") {
                        return "£" + row.discount_amount;
                    } else {
                        return row.discount_percent + "%";
                    }
                },
            },
            {
                aTargets: [3],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.max_discount !== null) {
                        return "£" + row.max_discount;
                    } else {
                        return "N/A";
                    }
                },
            },
            {
                aTargets: [5],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    return row.start_date + " To " + row.end_date;
                },
            },
            {
                aTargets: [4],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.min_order_amount !== null) {
                        return "£" + row.min_order_amount;
                    } else {
                        return "N/A";
                    }
                },
            },
            {
                aTargets: [8],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    let editUrlEn =
                        baseUrl + "/coupon/edit/" + row.id;
                    // let editUrlAr =
                    //     baseUrl + "/discounts/edit/" + row.id + "/ar";
                    let deleteUrl = baseUrl + "/coupon/delete/" + row.id;
                    return (
                        '<a class="action-class view-access editIcon" href="' +
                        editUrlEn +
                        '" id="edit_' +
                        row.id +
                        '"  ><i class="far fa-edit" aria-hidden="true"></i></a><a class="delete-record action-class deleteIcon m-2" href="javascript:void(0)" data-url="' +
                        deleteUrl +
                        '" onclick="confirmation(this);"  data-type="discount coupon" id="delete_record_' +
                        row.id +
                        '"><i class="fa fa-trash" aria-hidden="true"></i></a>'
                    );
                },
            },
            {
                aTargets: [7],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.status == 1) {
                        return '<span class="badge bg-success bg-glow">Acive</span>';
                    } else {
                        return '<span class="badge bg-danger bg-glow">In - Acive</span>';
                    }
                },
            },
        ],
        select: {
            style: "multi",
        },
        order: [[1, "asc"]],
    });

    $(".onlineAll").on("click", function (e) {
        let dt_user_table = $("#discountsTables").DataTable();
        console.log(dt_user_table);
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to active",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/coupons/online-all/1",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        }
                    },
                });
            }
        });
    });

    $(".offlineAll").on("click", function (e) {
        let dt_user_table = $("#discountsTables").DataTable();
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to inactive",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/coupons/online-all/0",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        }
                    },
                });
            }
        });
    });

    $(".DeleteALL").on("click", function (e) {
        let dt_user_table = $("#discountsTables").DataTable();
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        console.log(rows_selected.length);
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to delete",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/coupons/delete-all",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        }
                    },
                });
            }
        });
    });
}

//Testimonials
if ($("#testimonialsTables").length > 0) {
    $("#testimonialsTables").DataTable({
        dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"user_status mt-50 width-200"><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                className: "btn btn-danger me-2 DeleteALL",
                text: "Delete",
            },
            {
                className: "btn btn-success me-2 onlineAll",
                text: "Active",
            },
            {
                className: "btn btn-warning me-2 offlineAll",
                text: "InActive",
            },
            {
                extend: "collection",
                className: "btn btn-info dropdown-toggle me-2",
                text: "Export",
                buttons: [
                    {
                        extend: "print",
                        text: "Print",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4, 5],
                        },
                    },
                    {
                        extend: "csv",
                        text: "Csv",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4, 5],
                        },
                    },
                    {
                        extend: "excel",
                        text: "Excel",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4, 5],
                        },
                    },
                    {
                        extend: "pdf",
                        text: "Pdf",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4, 5],
                        },
                    },
                    {
                        extend: "copy",
                        text: "Copy",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [2, 3, 4, 5],
                        },
                    },
                ],
                init: function (api, node, config) {
                    $(node).removeClass("btn-secondary");
                    $(node).parent().removeClass("btn-group");
                    setTimeout(function () {
                        $(node)
                            .closest(".dt-buttons")
                            .removeClass("btn-group")
                            .addClass("d-inline-flex");
                    }, 50);
                },
            },
        ],
        initComplete: function () {
            this.api()
                .columns(4)
                .every(function () {
                    var column = this;
                    var select = $(
                        '<select id="orderStatus" class="form-select text-capitalize"><option value=""> Select Status </option><option value="1">Active</option><option value="0">Incative</option></select>'
                    )
                        .appendTo(".user_status")
                        .on("change", function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column
                                .search(val ? "" + val + "" : "", true, false)
                                .draw();
                        });
                });
        },
        retrieve: true,
        paging: true,
        processing: true,
        serverSide: true,
        ajax: baseUrl + "/testimonials/getTestimonials",
        pageLength: 10,
        language: {
            searchPlaceholder: "Search By Name",
        },
        columns: [
            {
                data: "id",
            },
            {
                data: "image",
            },
            {
                data: "name",
            },
            {
                data: "rating",
            },
            {
                data: "message",
            },
            {
                data: "status",
            },
        ],
        aoColumnDefs: [
            {
                targets: 0,
                orderable: false,
                checkboxes: {
                    selectRow: true,
                },
            },

            {
                aTargets: [1],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.image !== undefined && row.image !== null) {
                        return (
                            '<img class="round" src="' +
                            baseUrl +
                            "/" +
                            row.image +
                            '" alt="' +
                            row.name +
                            '" width="40px" height="40px" />'
                        );
                    } else {
                        return (
                            '<img class="round" src="' +
                            baseUrl +
                            '/assets/img/placeholder.png" alt="' +
                            row.name +
                            '" width="40px" height="40px" />'
                        );
                    }
                },
            },
            {
                aTargets: [2],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.parent_id !== 0) {
                        return "<p class='childNode'>" + row.name + "</p>";
                    } else {
                        return "<p>" + row.name + "</p>";
                    }
                },
            },
            {
                targets: 6,
                orderable: false,
            },
            {
                aTargets: [5],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.status == 1) {
                        return '<span class="badge bg-success bg-glow">Acive</span>';
                    } else {
                        return '<span class="badge bg-danger bg-glow">In-Active</span>';
                    }
                },
            },
            {
                aTargets: [6],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    let editUrl = baseUrl + "/testimonial/edit/" + row.id;
                    let viewUrl = baseUrl + "/testimonial/view/" + row.id;
                    let deleteUrl = baseUrl + "/testimonial/delete/" + row.id;
                    // <a class="action-class view-access editIcon" href="' +viewUrl +'" id="view_' +row.id +'"  ><i class="far fa-eye" aria-hidden="true"></i></a>
                    return (
                        '<a class="action-class view-access editIcon me-2" href="' +
                        editUrl +
                        '" id="edit_' +
                        row.id +
                        '"  ><i class="far fa-edit" aria-hidden="true"></i></a><a class="delete-record action-class deleteIcon" href="javascript:void(0)" data-url="' +
                        deleteUrl +
                        '" onclick="confirmation(this);"  data-type="product" id="delete_record_' +
                        row.id +
                        '"><i class="fa fa-trash" aria-hidden="true"></i></a>'
                    );
                },
            },
        ],
        select: {
            style: "multi",
        },
        order: [[2, "asc"]],
    });

    $(".onlineAll").on("click", function (e) {
        let dt_user_table = $("#testimonialsTables").DataTable();
        console.log(dt_user_table);
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to active",
            icon: "warning",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            // cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/testimonials/online-all/1",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                                document.location.reload();
                            }, 3000);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                                document.location.reload();
                            }, 3000);
                        }
                    },
                });
            }
        });
    });

    $(".offlineAll").on("click", function (e) {
        let dt_user_table = $("#testimonialsTables").DataTable();
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to inactive",
            icon: "warning",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            // cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/testimonials/online-all/0",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        }
                    },
                });
            }
        });
    });

    $(".DeleteALL").on("click", function (e) {
        let dt_user_table = $("#testimonialsTables").DataTable();
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        console.log(rows_selected.length);
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to delete",
            icon: "warning",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            // cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/testimonials/delete-all",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        }
                    },
                });
            }
        });
    });
}

//Enquiry
if ($("#enquiryTables").length > 0) {
    $("#enquiryTables").DataTable({
        dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"user_status mt-50 width-200"><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                className: "btn btn-danger me-2 DeleteALL",
                text: "Delete",
            },
            {
                extend: "collection",
                className: "btn btn-info dropdown-toggle me-2",
                text: "Export",
                buttons: [
                    {
                        extend: "print",
                        text: "Print",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5],
                        },
                    },
                    {
                        extend: "csv",
                        text: "Csv",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5],
                        },
                    },
                    {
                        extend: "excel",
                        text: "Excel",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5],
                        },
                    },
                    {
                        extend: "pdf",
                        text: "Pdf",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5],
                        },
                    },
                    {
                        extend: "copy",
                        text: "Copy",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5],
                        },
                    },
                ],
                init: function (api, node, config) {
                    $(node).removeClass("btn-secondary");
                    $(node).parent().removeClass("btn-group");
                    setTimeout(function () {
                        $(node)
                            .closest(".dt-buttons")
                            .removeClass("btn-group")
                            .addClass("d-inline-flex");
                    }, 50);
                },
            },
        ],
        retrieve: true,
        paging: true,
        processing: true,
        serverSide: true,
        ajax: baseUrl + "/enquiries/getEnquiries",
        pageLength: 10,
        language: {
            searchPlaceholder: "Search By Name",
        },
        columns: [
            {
                data: "id",
            },
            {
                data: "name",
            },
            {
                data: "email",
            },
            {
                data: "phone",
            },
            {
                data: "subject",
            },
            {
                data: "message",
            },
        ],
        aoColumnDefs: [
            {
                targets: 0,
                orderable: false,
                checkboxes: {
                    selectRow: true,
                },
            },

            {
                aTargets: [1],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.parent_id !== 0) {
                        return "<p class='childNode'>" + row.name + "</p>";
                    } else {
                        return "<p>" + row.name + "</p>";
                    }
                },
            },
            {
                targets: 6,
                orderable: false,
            },
            {
                aTargets: [6],
                mData: "id",

                mRender: function (data, type, row, meta) {
                    let viewUrl = baseUrl + "/enquiry/view/" + row.id;
                    let deleteUrl = baseUrl + "/enquiry/delete/" + row.id;

                    // View button
                    let viewButton = '<a class="action-class view-access viewIcon me-2" href="#" onclick="openViewContactModal(' +
                        row.id +
                        ')"><i class="far fa-eye" aria-hidden="true"></i></a>';

                    // Delete button
                    let deleteButton = '<a class="delete-record action-class deleteIcon" href="javascript:void(0)" data-url="' +
                        deleteUrl +
                        '" onclick="confirmation(this);" data-type="contact" id="delete_record_' +
                        row.id +
                        '"><i class="fa fa-trash" aria-hidden="true"></i></a>';

                    // Combine buttons and return
                    return viewButton + deleteButton;
                },

            },
        ],
        select: {
            style: "multi",
        },
        order: [[2, "asc"]],
    });

    function openViewContactModal(contactId) {
        $.ajax({
            type: "GET",
            url: baseUrl + "/enquiry/getEnquiryDetails/" + contactId,
            success: function (data) {
                let htmlContent = '<h5>Enquiry Details :</h5><hr>';
                htmlContent += '<p>Name : ' + data.productContact.name + '</p>';
                htmlContent += '<p>Email : ' + data.productContact.email + '</p>';
                htmlContent += '<p>Phone : ' + data.productContact.phone + '</p>';
                htmlContent += '<p>Enquiry Date : ' + formatDate(data.productContact.created_at) + '</p>';
                htmlContent += '<p>Subject : ' + data.productContact.subject + '</p>';
                htmlContent += '<p>Message : ' + data.productContact.message + '</p>';

                $('#viewContactModal .modal-body').html(htmlContent);
                $('#viewContactModal').modal('show');
            },
            error: function (error) {
                console.error("Error fetching contact details:", error);
            }
        });
    }

    function formatDate(dateString) {
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        const formattedDate = new Date(dateString).toLocaleDateString('en-US', options);
        return formattedDate;
    }

    $(".DeleteALL").on("click", function (e) {
        let dt_user_table = $("#enquiryTables").DataTable();
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        console.log(rows_selected.length);
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to delete",
            icon: "warning",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            // cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/enquiries/delete-all",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        }
                    },
                });
            }
        });
    });
}

function confirmation(e) {
    let id = $(e).attr("id");
    let urlRequest = $("#" + id).data("url");
    let deleteType = $("#" + id).data("type");
    Swal.fire({
        title: "Are you sure ?",
        text: "You want to delete this " + deleteType + ".",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        // cancelButtonText: "No",
        confirmButtonText: "Yes",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "GET",
                url: urlRequest,
                success: function (response) {
                    if (response.success == true) {
                        $("#" + id)
                            .closest("tr")
                            .remove();
                        console.log(window.location);
                        $(location).attr("href", window.location);
                    } else {
                        $(location).attr("href", window.location);
                    }
                },
            });
        }
    });
}


//Orders
if ($("#ordersTables").length > 0) {
    $("#ordersTables").DataTable({
        dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"user_status mt-50 width-200"><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                className: "btn btn-danger me-2 DeleteALL",
                text: "Delete",
            },
            {
                extend: "collection",
                className: "btn btn-info dropdown-toggle me-2",
                text: "Export",
                buttons: [
                    {
                        extend: "print",
                        text: "Print",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                        },
                    },
                    {
                        extend: "csv",
                        text: "Csv",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                        },
                    },
                    {
                        extend: "excel",
                        text: "Excel",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                        },
                    },
                    {
                        extend: "pdf",
                        text: "Pdf",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                        },
                    },
                    {
                        extend: "copy",
                        text: "Copy",
                        className: "dropdown-item",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                        },
                    },
                ],
                init: function (api, node, config) {
                    $(node).removeClass("btn-secondary");
                    $(node).parent().removeClass("btn-group");
                    setTimeout(function () {
                        $(node)
                            .closest(".dt-buttons")
                            .removeClass("btn-group")
                            .addClass("d-inline-flex");
                    }, 50);
                },
            },
        ],
        initComplete: function () {
            this.api()
                .columns(8)
                .every(function () {
                    var column = this;
                    var select = $(
                        '<select id="orderStatus" class="form-select text-capitalize"><option value=""> Select Status </option><option value="1">In-Process</option><option value="2">Completed</option><option value="3">Cancelled</option><option value="4">Refunded</option></select>'
                    )
                        .appendTo(".user_status")
                        .on("change", function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column
                                .search(val ? "" + val + "" : "", true, false)
                                .draw();
                        });
                });
        },
        retrieve: true,
        paging: true,
        processing: true,
        serverSide: true,
        ajax: baseUrl + "/orders/getOrders",
        pageLength: 10,
        language: {
            searchPlaceholder: "Search By Name",
        },
        columns: [
            {
                data: "id",
            },
            {
                data: "order_id",
            },
            {
                data: "customerName",
            },
            {
                data: "total_amount",
            },
            {
                data: "pay_amount",
            },
            {
                data: "discount_amount",
            },
            {
                data: "payment_type",
            },
            {
                data: "created_at",
            },
            {
                data: "status",
            },
            {
                data: "totalItems",
            },
        ],
        aoColumnDefs: [
            {
                targets: 0,
                orderable: false,
                checkboxes: {
                    selectRow: true,
                },
            },
            {
                aTargets: [9],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    let items = baseUrl + "/orders/items/view/" + row.id;
                    return (
                        '<span class="badge bg-label-success">' + row.totalItems + '</span>'
                        // '<a class="action-class view-access editIcon" href="' +
                        // items +
                        // '" id="items_' +
                        // row.id +
                        // '">' +
                        // '<span class="badge bg-label-success">' + row.totalItems + '</span>' +
                        // "</a>"
                    );
                },
            },
            {
                aTargets: [8],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    if (row.status == 1) {
                        return '<span class="badge bg-success bg-glow">In-Process</span>';
                    } else if (row.status == 2) {
                        return '<span class="badge bg-success bg-glow">Completed</span>';
                    } else if (row.status == 3) {
                        return '<span class="badge bg-success bg-glow">Cancelled</span>';
                    } else if (row.status == 4) {
                        return '<span class="badge bg-success bg-glow">Refunded</span>';
                    }
                },
            },
            {
                aTargets: [10],
                mData: "id",
                mRender: function (data, type, row, meta) {
                    let deleteUrl = baseUrl + "/orders/delete/" + row.id;
                    let viewUrl = baseUrl + "/orders/view/" + row.id;

                    return (
                        '<a class="action-class view-access editIcon me-2" href="' +
                        viewUrl +
                        '" id="view_' +
                        row.id +
                        '"><i class="far fa-eye" aria-hidden="true"></i></a><a class="delete-record action-class deleteIcon" href="javascript:void(0)" data-url="' +
                        deleteUrl +
                        '" onclick="confirmation(this);"  data-type="orders" id="delete_record_' +
                        row.id +
                        '"><i class="fa fa-trash" aria-hidden="true"></i></a>'
                    );
                },
            },
        ],
        select: {
            style: "multi",
        },
        order: [[1, "asc"]],
    });

    $(".DeleteALL").on("click", function (e) {
        let dt_user_table = $("#ordersTables").DataTable();
        var rows_selected = dt_user_table.column(0).checkboxes.selected();
        console.log(rows_selected.length);
        if (rows_selected.length < 1) {
            let html =
                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">Please select at least one</div></div>';
            $(".messageShowAlert").append(html);
            setTimeout(function () {
                $(".toast-autohide").remove();
            }, 3000);
            return false;
        }
        rows_selected = rows_selected.join(",");
        rows_selected = rows_selected.split(",");
        Swal.fire({
            title: "Are you sure ?",
            text: "You want to delete",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "No",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: baseUrl + "/orders/delete-all",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: rows_selected,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #288900;color: #f1f1f1;"><strong class="me-auto">Success</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        } else {
                            let html =
                                '<div class="toast toast-autohide show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false"><div class="toast-header" style="background: #ff0000;color: #fafbfd;"><strong class="me-auto">Danger!</strong><small class="text-muted" style="color:#ffffff!important">just now</small><button type="button" class="ms-1 btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">' +
                                response.message +
                                "</div></div>";
                            $(".messageShowAlert").append(html);
                            setTimeout(function () {
                                $(".toast-autohide").remove();
                            }, 3000);
                            $(location).attr("href", window.location);
                        }
                    },
                });
            }
        });
    });
}

function viewOrderDetailModal(element) {
    var urlRequest = element.getAttribute('data-url');

    $.ajax({
        type: "GET",
        url: urlRequest,
        success: function (data) {
            let order = data.order;
            let orderItem = data.orderItem;
            let product = data.product;
            let variation = data.variation;

            let htmlContent = '<h2>Order #' + order.order_id + '</h2><hr>';

            // Product details
            htmlContent += '<h3>Product Details</h3><hr>';
            htmlContent += '<p><strong>Name:</strong> ' + product.name + '</p>';
            htmlContent += '<p><strong>Order Number:</strong> ' + orderItem.order_number + '</p>';
            htmlContent += '<p><strong>Sale Price:</strong> ' + orderItem.sale_price + '</p>';
            htmlContent += '<p><strong>Quantity:</strong> ' + orderItem.quantity + '</p>';
            htmlContent += '<p><strong>Description:</strong> ' + product.description + '</p>';

            // Variation details (if available)
            if (variation) {
                htmlContent += '<h3>Variation</h3><hr>';
                htmlContent += '<p><strong>Variation Name:</strong> ' + variation.name + '</p>';
                htmlContent += '<p><strong>Variation Price:</strong> ' + variation.sale_price + '</p>';
                // Add more variation details as needed
            }

            // Images (if available)
            if (product.images && product.images.length > 0) {
                htmlContent += '<h3>Images</h3><hr>';
                htmlContent += '<div class="row">';
                product.images.forEach(function (image) {
                    htmlContent += '<div class="col-md-3"><img src="' + baseUrl + '/' + image.image_path + '" alt="Product Image" class="img-fluid"></div>';
                });
                htmlContent += '</div>';
            }

            $('#viewOrderDetailModal .modal-body').html(htmlContent);
            $('#viewOrderDetailModal').modal('show');
        },
        error: function (error) {
            console.error("Error fetching order details:", error);
        }
    });
}
