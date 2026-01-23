
$(document).ready(function () {
    const tableEl = $("#FormTable");
    const ajaxUrl = tableEl.data("url");

    const table = tableEl.DataTable({
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,
        columns: [
            { data: "id", name: "id" },
            { data: "name", name: "name" },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
            },
        ],
        order: [[0, "asc"]],
        responsive: true,
        pageLength: 10,
        lengthChange: true,
        dom: '<"table-responsive"rt><"d-flex justify-content-between mt-2 ps-3"<"length-div"l><"pagination-div pe-2"p>>i',
        drawCallback: function () {
            $(".dropdown-toggle").each(function () {
                new bootstrap.Dropdown(this);
            });

            $(".pagination-div .paginate_button")
                .addClass("btn btn-sm btn-primary mx-1")
                .removeClass("paginate_button");
        },
    });

    // Toggle search
    $("#toggleSearch").on("click", function () {
        $("#searchDiv").slideToggle(200, function () {
            if ($(this).is(":visible")) {
                $("#formSearch").focus();
            }
        });
    });

    // Search
    $("#formSearch").on("keyup", function () {
        table.search(this.value).draw();
    });
});
