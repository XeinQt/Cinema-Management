let mallTable;
let currentFilter = "";

//views
function initializeMallTable(filter = "") {
    currentFilter = filter;

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable("#mallsDatatables")) {
        $("#mallsDatatables").DataTable().destroy();
    }

    mallTable = $("#mallsDatatables").DataTable({
        ajax: {
            url: baseUrl() + "/MallsManagement/DataTables",
            data: function (d) {
                d.filter = filter;
            },
        },
        processing: true,
        serverSide: true,
        autoWidth: false,
        columns: [
            {
                data: "mall_id",
                name: "mall_id",
                title: "Mall ID",
                width: "10px",
            },
            {
                data: "name",
                name: "name",
                title: "Name",
                width: "100px",
            },
            {
                data: "location",
                name: "location",
                title: "Location",
                width: "150px",
            },
            {
                data: "description",
                name: "description",
                title: "Description",
                width: "150px",
                render: function (data) {
                    return `<div class="max-w-[200px] overflow-hidden whitespace-nowrap text-ellipsis" title="${data}">${data}</div>`;
                },
            },
            {
                data: "active",
                name: "active",
                title: "Status",
                width: "10px",
                render: function (data) {
                    return data == 1
                        ? '<span class="px-2 py-1 bg-green-500 text-white rounded-full text-sm">Active</span>'
                        : '<span class="px-2 py-1 bg-red-500 text-white rounded-full text-sm">Inactive</span>';
                },
            },
            {
                data: "action",
                name: "action",
                title: "Actions",
                orderable: false,
                searchable: false,
                width: "5px",
            },
        ],
        order: [[0, "desc"]],
        drawCallback: function () {
            document.getElementById("filter").value = currentFilter;
        },
    });
}

// Handle filter change
document.getElementById("filter").addEventListener("change", function () {
    initializeMallTable(this.value);
});

// Handle delete mall
$(document).on("click", ".delete-mall", function () {
    const mallId = $(this).attr("data-id");
    if (!mallId) {
        console.error("Mall ID not found");
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: "This mall will be deactivated!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, deactivate it!",
    }).then((result) => {
        if (result.isConfirmed) {
            updateMallStatus(mallId);
        }
    });
});

// Function to update mall status
async function updateMallStatus(mallId) {
    try {
        const response = await fetch(
            `${baseUrl()}/MallsManagement/updateStatus/${mallId}`,
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
            if (mallTable) {
                mallTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire(
            "Error!",
            error.message || "Failed to update mall status",
            "error"
        );
    }
}

// Handle restore mall
$(document).on("click", ".restore-mall", function () {
    const mallId = $(this).attr("data-id");
    if (!mallId) {
        console.error("Mall ID not found");
        return;
    }

    Swal.fire({
        title: "Restore Mall?",
        text: "This will reactivate the mall!",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, restore it!",
    }).then((result) => {
        if (result.isConfirmed) {
            restoreMallStatus(mallId);
        }
    });
});

// Function to restore mall status
async function restoreMallStatus(mallId) {
    try {
        const response = await fetch(
            `${baseUrl()}/MallsManagement/restore/${mallId}`,
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
            if (mallTable) {
                mallTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire("Error!", error.message || "Failed to restore mall", "error");
    }
}

// Handle edit mall
$(document).on("click", ".edit-mall", function () {
    const mallId = $(this).attr("data-id");
    if (!mallId) {
        console.error("Mall ID not found");
        return;
    }

    const row = mallTable.row($(this).closest("tr")).data();

    // Populate the edit form
    document.getElementById("edit_mall_id").value = mallId;
    document.getElementById("edit_name").value = row.name;
    document.getElementById("edit_location").value = row.location;
    document.getElementById("edit_description").value = row.description;

    // Open the edit modal
    openEditModal();
});

// Edit Mall Form
document
    .getElementById("editMallForm")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const mallId = formData.get("mall_id");
        if (!mallId) {
            console.error("Mall ID not found in form");
            return;
        }

        try {
            const response = await fetch(
                baseUrl() + `/MallsManagement/update/${mallId}`,
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
                    text: data.message || "Mall updated successfully.",
                });

                closeEditModal();

                // Refresh the table after updating a mall
                if (mallTable) {
                    mallTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to update Mall.",
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

function openEditModal() {
    const modal = document.getElementById("editMallModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeEditModal() {
    const modal = document.getElementById("editMallModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

//addd
function openModal() {
    const modal = document.getElementById("addMallModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeModal() {
    const modal = document.getElementById("addMallModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// add and edit
document.addEventListener("DOMContentLoaded", function () {
    // Add Mall Form
    document
        .getElementById("addMallForm")
        .addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch(
                    baseUrl() + "/MallsManagement/create",
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
                        text: data.message || "Mall added successfully.",
                    });

                    this.reset();
                    closeModal();

                    // Refresh the table after adding a mall
                    if (mallTable) {
                        mallTable.ajax.reload(null, false);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: data.message || "Failed to add Mall.",
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
