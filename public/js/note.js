flatpickr(".datepicker", {
    dateFormat: "d/m/Y",
    allowInput: true,
});

let notesTable;

// Load Notes tab content & initialize DataTable
function loadNotesTab() {
    $.get("{{ route('notes.view', $task->id) }}", function (html) {
        $("#notesList").html(html);

        const notesTableEl = $("#notesTable");

        if ($.fn.DataTable.isDataTable(notesTableEl)) {
            notesTableEl.DataTable().destroy();
        }

        // Inject search bar
        if (!$("#notesSearch").length) {
            const searchHtml = `
                <div class="ms-auto mb-2">
                    <input type="text" id="notesSearch" class="form-control form-control-sm" placeholder="Search notes...">
                </div>
            `;
            $("#notesHeader").append(searchHtml);
        }

        notesTable = notesTableEl.DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('notes.datatable', $task->id) }}",
            autoWidth: false,
            responsive: true,
            columns: [
                {
                    data: "id",
                    name: "id",
                },
                {
                    data: "note",
                    name: "note",
                },
                {
                    data: "created_at",
                    name: "created_at",
                },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                },
            ],
            order: [[0, "desc"]],
            pageLength: 10,
            lengthChange: true,
            dom: '<"table-responsive"rt><"d-flex justify-content-between mt-2"<"length-div"l><"pagination-div"p>>i',
            drawCallback: function () {
                $(".dropdown-toggle").each(function () {
                    new bootstrap.Dropdown(this);
                });
                $(".pagination-div .paginate_button")
                    .addClass("btn btn-sm btn-primary mx-1")
                    .removeClass("paginate_button");
            },
        });

        // Custom search input
        $("#notesSearch")
            .off("keyup")
            .on("keyup", function () {
                notesTable.search(this.value).draw();
            });
    }).fail(function () {
        console.error("Failed to load notes view");
    });
}

// Load Notes tab when activated
document.addEventListener("DOMContentLoaded", function () {
    var notesTab = document.querySelector("#notes-tab");
    notesTab.addEventListener("shown.bs.tab", function () {
        loadNotesTab();
    });
});

// Add Note via AJAX
$(document).on("submit", "#noteForm", function (e) {
    e.preventDefault();
    $.post("{{ route('notes.store') }}", $(this).serialize(), function () {
        $("#addNoteModal").modal("hide");
        $("#noteForm")[0].reset();
        loadNotesTab();
    });
});

// Delete Note via AJAX
$(document).on("click", ".deleteNote", function () {
    let button = $(this);
    let id = button.data("id");

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/notes/" + id,
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function (res) {
                    // Remove the row smoothly
                    let row = button.closest("tr");
                    row.fadeOut(300, function () {
                        $(this).remove();
                    });

                    // SweetAlert success message
                    Swal.fire(
                        "Deleted!",
                        "Your note has been deleted.",
                        "success",
                    );
                },
                error: function (xhr) {
                    Swal.fire(
                        "Error!",
                        "Failed to delete note. Try again.",
                        "error",
                    );
                    console.error(xhr.responseText);
                },
            });
        }
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const addBtn = document.getElementById("addNoteBtn");

    document
        .getElementById("notes-tab")
        .addEventListener("shown.bs.tab", function () {
            addBtn.classList.remove("d-none"); // show on Notes
        });

    document
        .getElementById("details-tab")
        .addEventListener("shown.bs.tab", function () {
            addBtn.classList.add("d-none"); // hide on Details
        });
});
