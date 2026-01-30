$(document).ready(function () {
    const tableEl = $("#TaskTable");
    const ajaxUrl = tableEl.data("url");

    const table = tableEl.DataTable({
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,
        autoWidth: false,
        responsive: true,
        columns: [
            // { data: "id", name: "id" },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: "title", name: "title" },
            { data: "description", name: "description" },
            { 
                data: "user", 
                name: "users.name", // Correctly maps to the many-to-many relationship
                orderable: false 
            },
            {
                data: "due", // Renders the "X Days" Red text or Date
                name: "due_days", // Uses the integer column for proper sorting
                className: "text-center",
            },
            { data: "status", name: "status" },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
            },
        ],
        order: [[0, "dec"]],
        pageLength: 10,
        dom: '<"table-responsive"rt><"d-flex justify-content-between mt-2 ps-3"<"length-div"l><"pagination-div pe-2"p>>i',
        drawCallback: function () {
            // Re-initialize Bootstrap Dropdowns so they work after table reload
            $(".dropdown-toggle").each(function () {
                new bootstrap.Dropdown(this);
            });

            // Clean up standard DataTables button styling
            $(".pagination-div .paginate_button")
                .addClass("btn btn-sm btn-primary mx-1")
                .removeClass("paginate_button");
        },
    });

    // Toggle Search Bar visibility
    $("#toggleSearch").on("click", function () {
        $("#searchDiv").slideToggle(200, function () {
            if ($(this).is(":visible")) {
                $("#taskSearch").focus();
            }
        });
    });

    // Custom search input handling
    $("#taskSearch").on("keyup", function () {
        table.search(this.value).draw();
    });
});
$(document).on('click', '.startTaskBtn', function () {
    let taskId = $(this).data('id');

    $.ajax({
        url: '/tasks/' + taskId + '/start',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            Swal.fire({
                icon: 'success',
                title: 'Started!',
                text: res.message,
                timer: 1800,
                showConfirmButton: false
            });

            // Reload only the datatable
            $('#TaskTable').DataTable().ajax.reload(null, false);
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: 'Something went wrong while starting the task.',
            });
        }
    });
});
$(document).on('submit', '.deleteTaskForm', function (e) {
    e.preventDefault();

    let form = this;
    let url = $(form).attr('action');
    let data = $(form).serialize(); // includes _token + _method

    let table = $('.dataTable').DataTable();

    Swal.fire({
        title: 'Are you sure?',
        text: "This will permanently delete the task.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'POST',      // form submits POST + _method=DELETE
                data: data,
                success: function () {
                    Swal.fire('Deleted!', 'Task has been removed.', 'success');
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    console.log(xhr.status, xhr.responseText);
                    Swal.fire('Error!', 'Could not delete task.', 'error');
                }
            });
        }
    });
});
