// // Initialize datepicker & timepicker
// $(".datepicker").flatpickr({
//     dateFormat: "d/m/Y",
// });

// $(".timepicker").timepicker({
//     timeFormat: "h:mm p",
// });

// // Flash messages
// if (window.flash?.success) {
//     Swal.fire({
//         icon: "success",
//         title: "Success",
//         text: window.flash.success,
//         timer: 2000,
//         showConfirmButton: false,
//     });
// }

// if (window.flash?.error) {
//     Swal.fire({
//         icon: "error",
//         title: "Error",
//         text: window.flash.error,
//     });
// }

// if (window.flash?.validationErrors?.length > 0) {
//     Swal.fire({
//         icon: "warning",
//         title: "Validation Error",
//         html: window.flash.validationErrors.join("<br>"),
//     });
// }

// // SweetAlert delete confirmation
// document.addEventListener("click", function (e) {
//     if (e.target.classList.contains("delete-btn")) {
//         e.preventDefault(); // Prevent immediate form submission
//         const form = e.target.closest("form"); // Get the parent form

//         Swal.fire({
//             title: "Are you sure?",
//             text: "Do you want to delete this item!",
//             icon: "warning",
//             showCancelButton: true,
//             confirmButtonColor: "#d33",
//             cancelButtonColor: "#3085d6",
//             confirmButtonText: "Yes, delete it",
//             cancelButtonText: "Cancel",
//         }).then((result) => {
//             if (result.isConfirmed) {
//                 form.submit(); // Submit the form if confirmed
//             }
//         });
//     }
// });
// // // Sidebar toggle script
// // const sidebar = document.getElementById("sidebar");
// // const toggleBtn = document.getElementById("sidebarToggle");

// // // Immediately apply minimized class on page load to prevent jump
// // if (localStorage.getItem("sidebarMinimized") === "true") {
// //     sidebar.classList.add("minimized");
// // }

// // // Toggle button click
// // toggleBtn.addEventListener("click", function () {
// //     sidebar.classList.toggle("minimized");

// //     // Save state in localStorage
// //     localStorage.setItem(
// //         "sidebarMinimized",
// //         sidebar.classList.contains("minimized")
// //     );
// // });
// // document.addEventListener("DOMContentLoaded", function () {
// //     const sidebar = document.querySelector(".sidebar");
// //     const toggleBtn = document.querySelector(".sidebar-toggle");

// //     // Toggle sidebar manually
// //     toggleBtn.addEventListener("click", function (e) {
// //         e.stopPropagation(); // prevent hover conflict
// //         sidebar.classList.toggle("minimized");
// //     });

// //     // Prevent sidebar collapsing while hovering
// //     sidebar.addEventListener("mouseenter", function () {
// //         sidebar.classList.add("hover-open");
// //     });

// //     sidebar.addEventListener("mouseleave", function () {
// //         sidebar.classList.remove("hover-open");
// //     });
// // });

// document.addEventListener("DOMContentLoaded", function () {
//     const sidebar = document.querySelector(".sidebar");
//     const toggleBtn = document.getElementById("sidebarToggle");

//     /* Restore state instantly (NO animation jump) */
//     if (localStorage.getItem("sidebarMinimized") === "true") {
//         sidebar.classList.add("minimized");
//     }

//     /* Toggle button */
//     toggleBtn.addEventListener("click", function (e) {
//         e.stopPropagation();

//         sidebar.classList.toggle("minimized");
//         localStorage.setItem(
//             "sidebarMinimized",
//             sidebar.classList.contains("minimized"),
//         );
//     });

//     /*  PREVENT SIDEBAR EXPAND ON LINK CLICK */
//     sidebar.querySelectorAll("a").forEach((link) => {
//         link.addEventListener("click", function () {
//             // Do NOTHING — sidebar state remains unchanged
//             sidebar.classList.remove("hover-open");
//         });
//     });

//     /* Optional: hover preview ONLY if minimized */
//     sidebar.addEventListener("mouseenter", () => {
//         if (sidebar.classList.contains("minimized")) {
//             sidebar.classList.add("hover-open");
//         }
//     });

//     sidebar.addEventListener("mouseleave", () => {
//         sidebar.classList.remove("hover-open");
//     });
// });

// document.addEventListener("DOMContentLoaded", function () {
//     const startDate = document.getElementById("start_date");
//     const endDate = document.getElementById("end_date");

//     startDate.addEventListener("change", function () {
//         endDate.min = this.value;
//         if (endDate.value && endDate.value < this.value) {
//             endDate.value = "";
//         }
//     });
// });

// flatpickr("#start_date", {
//     dateFormat: "d/m/Y",
//     minDate: "today",
//     onChange: function (selectedDates, dateStr) {
//         endPicker.set("minDate", dateStr);
//     },
// });

// flatpickr("#monthly_date", {
//     dateFormat: "d/m/Y",
//     minDate: "today",
//     onChange: function (selectedDates, dateStr) {
//         endPicker.set("minDate", dateStr);
//     },
// });
// const endPicker = flatpickr("#end_date", {
//     dateFormat: "d/m/Y",
//     minDate: "today",
// });

// document.getElementById("taskForm").addEventListener("submit", function (e) {
//     const startDate = start_date.value;
//     const endDate = end_date.value;
//     const startTime = document.querySelector('[name="start_time"]').value;
//     const endTime =
//         document.querySelector('[name="end_time"]').value || "23:59";

//     if (!endDate) return;

//     const [sd, sm, sy] = startDate.split("/");
//     const [ed, em, ey] = endDate.split("/");

//     const start = new Date(`${sy}-${sm}-${sd} ${startTime}`);
//     const end = new Date(`${ey}-${em}-${ed} ${endTime}`);
// });

// $(document).ready(function () {
//     function toggleFields() {
//         let type = $("#recurring").val();
//         $("#oneTimeFields, #daily_input, #weekly_input, #monthly_input").hide();

//         if (type === "daily") {
//             $("#daily_input").show();
//         } else if (type === "weekly") {
//             $("#weekly_input").show();
//         } else if (type === "monthly") {
//             $("#monthly_input").show();
//         } else {
//             $("#oneTimeFields").show();
//         }
//     }

//     toggleFields();
//     $("#recurring").change(function () {
//         toggleFields();
//     });
// });
/* ===========================
   Datepicker & Timepicker
=========================== */
if (window.flatpickr) {
    document.querySelectorAll(".datepicker").forEach(el => {
        flatpickr(el, { dateFormat: "d/m/Y" });
    });
}

/* ===========================
   Flash Messages (SweetAlert)
=========================== */
if (window.flash?.success) {
    Swal.fire({ icon: "success", title: "Success", text: window.flash.success, timer: 2000, showConfirmButton: false });
}
if (window.flash?.error) {
    Swal.fire({ icon: "error", title: "Error", text: window.flash.error });
}
if (window.flash?.validationErrors?.length > 0) {
    Swal.fire({ icon: "warning", title: "Validation Error", html: window.flash.validationErrors.join("<br>") });
}

/* ===========================
   SweetAlert Delete Confirm
=========================== */
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".delete-btn");
    if (!btn) return;

    e.preventDefault();
    const form = btn.closest("form");
    if (!form) return;

    Swal.fire({
        title: "Are you sure?",
        text: "Do you want to delete this item!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it",
        cancelButtonText: "Cancel",
    }).then(result => result.isConfirmed && form.submit());
});

/* ===========================
   Sidebar Toggle
=========================== */
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("sidebarToggle");
    if (!sidebar || !toggleBtn) return;

    if (localStorage.getItem("sidebarMinimized") === "true") {
        sidebar.classList.add("minimized");
    }

    toggleBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        sidebar.classList.toggle("minimized");
        localStorage.setItem("sidebarMinimized", sidebar.classList.contains("minimized"));
    });
});

/* ===========================
   Flatpickr Instances
=========================== */
document.addEventListener("DOMContentLoaded", function () {

    let endPicker = null; // ✅ declare ONCE

    if (window.flatpickr && document.getElementById("start_date")) {
        flatpickr("#start_date", {
            dateFormat: "d/m/Y",
            minDate: "today",
            onChange: function (_, dateStr) {
                if (endPicker) endPicker.set("minDate", dateStr);
            }
        });
    }

    if (window.flatpickr && document.getElementById("monthly_date")) {
        flatpickr("#monthly_date", {
            dateFormat: "d/m/Y",
            minDate: "today",
            onChange: function (_, dateStr) {
                if (endPicker) endPicker.set("minDate", dateStr);
            }
        });
    }

    if (window.flatpickr && document.getElementById("end_date")) {
        endPicker = flatpickr("#end_date", {
            dateFormat: "d/m/Y",
            minDate: "today",
        });
    }
});

/* ===========================
   Recurring Fields Toggle
=========================== */
$(document).ready(function () {
    function toggleFields() {
        let type = $("#recurring").val();
        $("#oneTimeFields, #daily_input, #weekly_input, #monthly_input").hide();

        if (type === "daily") $("#daily_input").show();
        else if (type === "weekly") $("#weekly_input").show();
        else if (type === "monthly") $("#monthly_input").show();
        else $("#oneTimeFields").show();
    }

    if ($("#recurring").length) {
        toggleFields();
        $("#recurring").on("change", toggleFields);
    }
});

/* ===========================
   Select2 User Search
=========================== */
document.addEventListener('DOMContentLoaded', function () {

    if (window.$ && $.fn.select2 && $('#user_id').length) {
        $('#user_id').select2({
            width: '100%',
            placeholder: 'Select User',
            ajax: {
                url: '/users/search',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: data.map(u => ({ id: u.id, text: u.name }))
                }),
                cache: true
            }
        });

        if (window.__OLD_USER__) {
            let u = window.__OLD_USER__;
            let option = new Option(u.name, u.id, true, true);
            $('#user_id').append(option).trigger('change');
        }
    }

});
