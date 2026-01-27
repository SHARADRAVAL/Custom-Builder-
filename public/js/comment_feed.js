$(document).ready(function () {
    $("#commentFeedbackForm").on("submit", function (e) {
        e.preventDefault();

        // Safety check for global variable
        if (!window.taskAppData || !window.taskAppData.commentFeedbackUrl) {
            console.error("Route configuration missing.");
            return;
        }

        let formData = $(this).serialize();

        $.ajax({
            url: window.taskAppData.commentFeedbackUrl,
            type: "POST",
            data: formData,
            success: function (res) {
                // 1. Hide the modal
                $("#commentFeedbackModal").modal("hide");

                // 2. Sync the values to the main "Details" form inputs
                // This makes the UI feel reactive
                $("#main_feedback_select").val($("#modal_feedback").val());
                $("#main_comment_textarea").val($("#modal_comment").val());

                // 3. Show success message
                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false,
                });
            },
            error: function (xhr) {
                let message = "Something went wrong. Please try again.";

                if (xhr.status === 422) {
                    // Validation errors
                    message = Object.values(xhr.responseJSON.errors)
                        .flat()
                        .join("\n");
                } else if (xhr.status === 419) {
                    // CSRF/Session expired
                    message = "Session expired. Please refresh the page.";
                }

                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: message,
                });

                console.error("Server Response:", xhr.responseText);
            },
        });
    });
});

$(document).on("click", "#startTaskBtn", function () {
    Swal.fire({
        title: "Start this task?",
        text: "Status will change to In Progress!",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        confirmButtonText: "Yes, start it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/tasks/" + window.taskAppData.taskId + "/start",
                type: "POST",
                data: {
                    _token: window.taskAppData.csrfToken,
                },
                success: function (res) {
                    Swal.fire("Started!", res.message, "success");
                    $("#startTaskBtn").replaceWith(
                        '<span class="badge text-dark text-capitalize text-start">in_progress</span>',
                    );
                },
                error: function () {
                    Swal.fire("Error!", "Failed to start task.", "error");
                },
            });
        }
    });
});

// Toggle "Add Note" button visibility based on tab
$('a[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
    if (e.target.id === "notes-tab") {
        $("#addNoteBtn").removeClass("d-none");
    } else {
        $("#addNoteBtn").addClass("d-none");
    }
});

// Initialize Flatpickr
flatpickr(".datepicker", {
    dateFormat: "d/m/Y",
    allowInput: true,
});
