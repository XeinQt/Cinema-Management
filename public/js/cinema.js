let cinemaTable;
let currentFilter = "";

const dropdownCache = {
    malls: null,
    managers: null,
};

function initializeCinemaTable(filter = "") {
    currentFilter = filter;

    if ($.fn.DataTable.isDataTable("#cinemasDatatables")) {
        $("#cinemasDatatables").DataTable().destroy();
    }

    cinemaTable = $("#cinemasDatatables").DataTable({
        ajax: {
            url: baseUrl() + "/CinemasManagement/DataTables",
            data: function (d) {
                d.filter = filter;
            },
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        columns: [
            {
                data: "cinema_id",
                name: "cinema_id",
                title: "ID",
                width: "50px",
            },
            { data: "mall_name", name: "mall_name", title: "Mall" },
            { data: "manager_name", name: "manager_name", title: "Manager" },
            { data: "name", name: "name", title: "Name" },
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
                data: "action",
                name: "action",
                title: "Actions",
                orderable: false,
                searchable: false,
                width: "100px",
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
    initializeCinemaTable(this.value);
});

// Handle delete cinema
$(document).on("click", ".delete-cinema", function () {
    const cinemaId = $(this).attr("data-id");
    if (!cinemaId) {
        console.error("Cinema ID not found");
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: "This cinema will be deactivated!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, deactivate it!",
    }).then((result) => {
        if (result.isConfirmed) {
            updateCinemaStatus(cinemaId);
        }
    });
});

// Function to update cinema status
async function updateCinemaStatus(cinemaId) {
    try {
        const response = await fetch(
            `${baseUrl()}/CinemasManagement/updateStatus/${cinemaId}`,
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
            if (cinemaTable) {
                cinemaTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire(
            "Error!",
            error.message || "Failed to update cinema status",
            "error"
        );
    }
}

// Handle restore cinema
$(document).on("click", ".restore-cinema", function () {
    const cinemaId = $(this).attr("data-id");
    if (!cinemaId) {
        console.error("Cinema ID not found");
        return;
    }

    Swal.fire({
        title: "Restore Cinema?",
        text: "This will reactivate the cinema!",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, restore it!",
    }).then((result) => {
        if (result.isConfirmed) {
            restoreCinemaStatus(cinemaId);
        }
    });
});

// Function to restore cinema status
async function restoreCinemaStatus(cinemaId) {
    try {
        const response = await fetch(
            `${baseUrl()}/CinemasManagement/restore/${cinemaId}`,
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
            if (cinemaTable) {
                cinemaTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire(
            "Error!",
            error.message || "Failed to restore cinema",
            "error"
        );
    }
}

// Handle edit cinema
$(document).on("click", ".edit-cinema", function () {
    const cinemaId = $(this).attr("data-id");
    if (!cinemaId) {
        console.error("Cinema ID not found");
        return;
    }

    const row = cinemaTable.row($(this).closest("tr")).data();

    // Populate the edit form
    document.getElementById("edit_cinema_id").value = cinemaId;
    document.getElementById("edit_cinema_name").value = row.name;

    // Populate mall and manager dropdowns
    populateEditDropdowns().then(() => {
        // Set selected values after dropdowns are populated
        const mallSelect = document.getElementById("edit_mall_select");
        const managerSelect = document.getElementById("edit_manager_select");

        // Find and select the mall option
        Array.from(mallSelect.options).forEach((option) => {
            if (option.textContent === row.mall_name) {
                option.selected = true;
            }
        });

        // Find and select the manager option
        Array.from(managerSelect.options).forEach((option) => {
            if (option.textContent === row.manager_name) {
                option.selected = true;
            }
        });
    });

    // Open the edit modal
    openEditModal();
});

// Edit Cinema Form
document
    .getElementById("editCinemaForm")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const cinemaId = formData.get("cinema_id");
        if (!cinemaId) {
            console.error("Cinema ID not found in form");
            return;
        }

        try {
            const response = await fetch(
                baseUrl() + `/CinemasManagement/update/${cinemaId}`,
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
                    text: data.message || "Cinema updated successfully.",
                });

                closeEditModal();

                // Refresh the table after updating a cinema
                if (cinemaTable) {
                    cinemaTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to update Cinema.",
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

// Modal Functions
function openModal() {
    const modal = document.getElementById("addCinemaModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    populateDropdowns();
}



function closeEditModal() {
    const modal = document.getElementById("editCinemaModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Function to populate mall and manager dropdowns for add
async function populateDropdowns() {
    try {
        // Fetch malls
        const mallsResponse = await fetch(
            baseUrl() + "/MallsManagement/DataTables"
        );
        const mallsData = await mallsResponse.json();
        const mallSelect = document.getElementById("mall_select");

        // Clear existing options except the first one
        while (mallSelect.options.length > 1) {
            mallSelect.remove(1);
        }

        // Filter for active malls only
        const activeMalls = mallsData.data.filter((mall) => mall.active === 1);

        activeMalls.forEach((mall) => {
            const option = document.createElement("option");
            option.value = mall.mall_id;
            option.textContent = mall.name;
            mallSelect.appendChild(option);
        });

        // Fetch managers
        const managersResponse = await fetch(
            baseUrl() + "/ManagersManagement/DataTables"
        );
        const managersData = await managersResponse.json();
        const managerSelect = document.getElementById("manager_select");

        // Clear existing options except the first one
        while (managerSelect.options.length > 1) {
            managerSelect.remove(1);
        }

        // Filter for active managers only
        const activeManagers = managersData.data.filter(
            (manager) => manager.active === 1
        );

        activeManagers.forEach((manager) => {
            const option = document.createElement("option");
            option.value = manager.manager_id;
            option.textContent = manager.first_name + " " + manager.last_name;
            managerSelect.appendChild(option);
        });
    } catch (error) {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to load dropdown data",
        });
    }
}

// Function to populate edit form dropdowns
async function populateEditDropdowns() {
    try {
        // Fetch malls
        const mallsResponse = await fetch(
            baseUrl() + "/MallsManagement/DataTables"
        );
        const mallsData = await mallsResponse.json();
        const mallSelect = document.getElementById("edit_mall_select");

        // Clear existing options
        mallSelect.innerHTML = '<option value="">Select Mall</option>';

        // Filter for active malls only
        const activeMalls = mallsData.data.filter((mall) => mall.active === 1);

        activeMalls.forEach((mall) => {
            const option = document.createElement("option");
            option.value = mall.mall_id;
            option.textContent = mall.name;
            mallSelect.appendChild(option);
        });

        // Fetch managers
        const managersResponse = await fetch(
            baseUrl() + "/ManagersManagement/DataTables"
        );
        const managersData = await managersResponse.json();
        const managerSelect = document.getElementById("edit_manager_select");

        // Clear existing options
        managerSelect.innerHTML = '<option value="">Select Manager</option>';

        // Filter for active managers only
        const activeManagers = managersData.data.filter(
            (manager) => manager.active === 1
        );

        activeManagers.forEach((manager) => {
            const option = document.createElement("option");
            option.value = manager.manager_id;
            option.textContent = manager.first_name + " " + manager.last_name;
            managerSelect.appendChild(option);
        });
    } catch (error) {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to load dropdown data",
        });
    }
}

//add

function closeModal() {
    const modal = document.getElementById("addCinemaModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

function openEditModal() {
    const modal = document.getElementById("editCinemaModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

// Initialize form submission
document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("addCinemaForm")
        ?.addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch(
                    baseUrl() + "/CinemasManagement/create",
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
                        text: data.message || "Cinema added successfully.",
                    });

                    this.reset();
                    closeModal();

                    // Refresh the table after adding a cinema
                    if (cinemaTable) {
                        cinemaTable.ajax.reload(null, false);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: data.message || "Failed to add Cinema.",
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

    // Initialize the table
    initializeCinemaTable();
});
