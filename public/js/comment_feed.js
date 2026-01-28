$(function () {
    // ---------- Helpers ----------
    const showSuccess = (title, text) => {
        Swal.fire({ icon: "success", title, text, timer: 2000, showConfirmButton: false });
    };

    const showError = (title, text) => {
        Swal.fire({ icon: "error", title, text });
    };

    const getAjaxErrorMessage = (xhr) => {
        if (xhr.status === 422) {
            return Object.values(xhr.responseJSON.errors).flat().join("\n");
        }
        if (xhr.status === 419) {
            return "Session expired. Please refresh the page.";
        }
        return "Something went wrong. Please try again.";
    };

    // ---------- Comment & Feedback ----------
    $("#commentFeedbackForm").on("submit", function (e) {
        e.preventDefault();

        if (!window.taskAppData?.commentFeedbackUrl) {
            console.error("Route configuration missing.");
            return;
        }

        const $form = $(this);

        $.post(window.taskAppData.commentFeedbackUrl, $form.serialize())
            .done((res) => {
                $("#commentFeedbackModal").modal("hide");

                // Sync modal → main form
                $("#main_feedback_select").val($("#modal_feedback").val());
                $("#main_comment_textarea").val($("#modal_comment").val());

                showSuccess("Updated!", res.message);
            })
            .fail((xhr) => {
                const message = getAjaxErrorMessage(xhr);
                showError("Error!", message);
                console.error("Server Response:", xhr.responseText);
            });
    });

    // ---------- Start Task ----------
    $(document).on("click", "#startTaskBtn", function () {
        Swal.fire({
            title: "Start this task?",
            text: "Status will change to In Progress!",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            confirmButtonText: "Yes, start it!",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post(`/tasks/${window.taskAppData.taskId}/start`, {
                _token: window.taskAppData.csrfToken,
            })
                .done((res) => {
                    showSuccess("Started!", res.message);
                    $("#startTaskBtn").replaceWith(
                        `<span class="badge text-dark text-capitalize text-start">in_progress</span>`
                    );
                })
                .fail(() => {
                    showError("Error!", "Failed to start task.");
                });
        });
    });

    // ---------- Tab Toggle ----------
    $('a[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
        $("#addNoteBtn").toggleClass("d-none", e.target.id !== "notes-tab");
    });

    // ---------- Datepicker ----------
    flatpickr(".datepicker", {
        dateFormat: "d/m/Y",
        allowInput: true,
    });
});
