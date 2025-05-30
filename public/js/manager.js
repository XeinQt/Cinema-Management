let managerTable;
let currentFilter = "";

function initializeManagerTable(filter = "") {
    currentFilter = filter;

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable("#managerTable")) {
        $("#managerTable").DataTable().destroy();
    }

    managerTable = $("#managerTable").DataTable({
        ajax: {
            url: baseUrl() + "/ManagersManagement/DataTables",
            data: function (d) {
                d.filter = filter;
            },
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        columnDefs: [
            {
                targets: 6, // ID column
                width: "50px",
                className: "item-start",
            },
        ],
        columns: [
            { data: "manager_id", name: "manager_id", title: "ID" },
            { data: "first_name", name: "first_name", title: "First Name" },
            { data: "last_name", name: "last_name", title: "Last Name" },
            { data: "email", name: "email", title: "Email" },
            { data: "phonenumber", name: "phonenumber", title: "Phone Number" },
            {
                data: "active",
                name: "active",
                title: "Status",
                render: function (data) {
                    return data == 1
                        ? '<span class="px-2 py-1 bg-green-500 text-white rounded-full text-sm">Active</span>'
                        : '<span class="px-2 py-1 bg-red-500 text-white rounded-full text-sm">Inactive</span>';
                },
            },
            {
                data: null,
                title: "Actions",
                orderable: false,
                render: function (data, type, row) {
                    let buttons = "";

                    // Edit button - show for both active and inactive
                    buttons += `<button class="edit-manager bg-blue-500 text-white px-2 py-1 rounded mr-2" data-id="${row.manager_id}">
                        <i class="fas fa-edit"></i> Edit
                    </button>`;

                    // Show different buttons based on active status
                    if (row.active == 1) {
                        buttons += `<button class="delete-manager bg-red-500 text-white px-2 py-1 rounded" data-id="${row.manager_id}">
                            <i class="fas fa-trash"></i> Deactivate
                        </button>`;
                    } else {
                        buttons += `<button class="restore-manager bg-green-500 text-white px-2 py-1 rounded" data-id="${row.manager_id}">
                            <i class="fas fa-undo"></i> Restore
                        </button>`;
                    }

                    return buttons;
                },
            },
        ],
        order: [[0, "desc"]], // Order by ID descending
        drawCallback: function () {
            // Update the filter dropdown to match current state
            document.getElementById("filter").value = currentFilter;
        },
    });
}

// Handle filter change
document.getElementById("filter").addEventListener("change", function () {
    initializeManagerTable(this.value);
});

// Handle delete manager
$(document).on("click", ".delete-manager", function () {
    const managerId = $(this).data("id");

    Swal.fire({
        title: "Are you sure?",
        text: "This manager will be deactivated!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, deactivate it!",
    }).then((result) => {
        if (result.isConfirmed) {
            updateManagerStatus(managerId);
        }
    });
});

// Function to update manager status
async function updateManagerStatus(managerId) {
    try {
        const response = await fetch(
            `${baseUrl()}/ManagersManagement/updateStatus/${managerId}`,
            {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            }
        );

        const data = await response.json();

        if (data.success) {
            Swal.fire("Deactivated!", data.message, "success");
            // Reload the DataTable to reflect the changes
            if (managerTable) {
                managerTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire(
            "Error!",
            error.message || "Failed to update manager status",
            "error"
        );
    }
}

// Handle restore manager
$(document).on("click", ".restore-manager", function () {
    const managerId = $(this).data("id");

    Swal.fire({
        title: "Restore Manager?",
        text: "This will reactivate the manager!",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, restore it!",
    }).then((result) => {
        if (result.isConfirmed) {
            restoreManagerStatus(managerId);
        }
    });
});

// Function to restore manager status
async function restoreManagerStatus(managerId) {
    try {
        const response = await fetch(
            `${baseUrl()}/ManagersManagement/restore/${managerId}`,
            {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            }
        );

        const data = await response.json();

        if (data.success) {
            Swal.fire("Restored!", data.message, "success");
            // Reload the DataTable to reflect the changes
            if (managerTable) {
                managerTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire(
            "Error!",
            error.message || "Failed to restore manager",
            "error"
        );
    }
}

// Modal Functions
function openModal() {
    const modal = document.getElementById("addManagerModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeModal() {
    const modal = document.getElementById("addManagerModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Initialize form submission
document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("addManagerForm")
        ?.addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch(
                    baseUrl() + "/ManagersManagement/create",
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                            Accept: "application/json",
                        },
                        body: formData,
                    }
                );

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: data.message || "Manager added successfully.",
                    });

                    this.reset();
                    closeModal();

                    // Refresh the table after adding a manager
                    if (managerTable) {
                        managerTable.ajax.reload(null, false);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: data.message || "Failed to add Manager.",
                    });
                }
            } catch (error) {
                console.error("Fetch Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Unexpected Error",
                    text: "Something went wrong. Please try again.",
                });
            }
        });
});

//kuhaon ang value sa modal
function openEditModal() {
    const modal = document.getElementById("editManagerModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeEditModal() {
    const modal = document.getElementById("editManagerModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

$(document).on("click", ".edit-manager", function () {
    const managerId = $(this).data("id");
    const row = managerTable.row($(this).closest("tr")).data();

    // Populate the edit form
    document.getElementById("edit_manager_id").value = managerId;
    document.getElementById("edit_first_name").value = row.first_name;
    document.getElementById("edit_last_name").value = row.last_name;
    document.getElementById("edit_email").value = row.email;
    document.getElementById("edit_phonenumber").value = row.phonenumber;

    // Open the edit modal
    openEditModal();
});

// Edit Manager Form
document
    .getElementById("editManagerForm")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const managerId = formData.get("manager_id");

        try {
            const response = await fetch(
                baseUrl() + `/ManagersManagement/update/${managerId}`,
                {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                        Accept: "application/json",
                    },
                    body: formData,
                }
            );

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: data.message || "Manager updated successfully.",
                });

                closeEditModal();

                // Refresh the table after updating a manager
                if (managerTable) {
                    managerTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to update Manager.",
                });
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            Swal.fire({
                icon: "error",
                title: "Unexpected Error",
                text: "Something went wrong. Please try again.",
            });
        }
    });
