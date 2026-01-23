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
            {
                data: "id",
                name: "id",
            },
            {
                data: "title",
                name: "title",
            },
            {
                data: "description",
                name: "description",
            },
            {
                data: "user",
                name: "user",
            },
            {
                data: "action",
                name: "action",
                // orderable: false,
                searchable: false,
            },
        ],
        order: [[0, "des"]],
        pageLength: 10,
        lengthChange: true,
        dom: '<"table-responsive"rt><"d-flex justify-content-between  mt-2 ps-3"<"length-div"l><"pagination-div pe-2"p>>i',
        drawCallback: function () {
            $(".dropdown-toggle").each(function () {
                new bootstrap.Dropdown(this);
            });

            $(".pagination-div .paginate_button")
                .addClass("btn btn-sm btn-primary mx-1")
                .removeClass("paginate_button");
        },
    });

    // Toggle search input
    $("#toggleSearch").on("click", function () {
        $("#searchDiv").slideToggle(200, function () {
            if ($(this).is(":visible")) {
                $("#taskSearch").focus();
            }
        });
    });

    // Search
    $("#taskSearch").on("keyup", function () {
        table.search(this.value).draw();
    });
});

$(document).ready(function () {
    $(document).on("submit", ".start-task-form", function (e) {
        e.preventDefault();
        let form = $(this);
        let recurringId = form.data("recurring-id");

        $.ajax({
            url: "/recurring-tasks/" + recurringId + "/start",
            type: "POST",
            data: form.serialize(),
            success: function (res) {
                // Update status badge and button like before
            },
        });
    });

    $(document).on("submit", ".complete-task-form", function (e) {
        e.preventDefault();
        let form = $(this);
        let recurringId = form.data("recurring-id");

        $.ajax({
            url: "/recurring-tasks/" + recurringId + "/complete",
            type: "POST",
            data: form.serialize(),
            success: function (res) {
                // Update status badge and button like before
            },
        });
    });
});
