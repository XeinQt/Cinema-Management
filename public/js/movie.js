let movieTable;
let currentFilter = "";

function initializeMovieTable(filter = "") {
    console.log("Initializing movie table with filter:", filter);
    currentFilter = filter;

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable("#movieTable")) {
        console.log("Destroying existing DataTable");
        $("#movieTable").DataTable().destroy();
    }

    console.log("Creating new DataTable instance");
    console.log("AJAX URL:", baseUrl() + "/MoviesManagement/DataTables");

    movieTable = $("#movieTable").DataTable({
        ajax: {
            url: "/MoviesManagement/DataTables",
            type: "GET",
            data: function (d) {
                d.filter = filter;
                return d;
            },
            dataSrc: function (json) {
                return json.data;
            },
            error: function (xhr, error, thrown) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to load movie data. Please try again.",
                });
            },
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: false,
        columns: [
            {
                data: "movie_id",
                name: "movie_id",
                title: "ID",
                width: "50px",
                searchable: true,
                orderable: true
            },
            { data: "title", name: "title", title: "Title", width: "150px" },
            { data: "genre", name: "genre", title: "Genre", width: "120px" },
            {
                data: "duration",
                name: "duration",
                title: "Duration",
                width: "100px",
            },
            {
                data: "description",
                name: "description",
                title: "Description",
                width: "250px",
            },
            { data: "rating", name: "rating", title: "Rating", width: "80px" },
            {
                data: "active",
                name: "active",
                title: "Status",
                width: "100px",
                render: function (data) {
                    return data == 1
                        ? '<span class="px-2 py-1 bg-green-500 text-white rounded-full text-sm">Active</span>'
                        : '<span class="px-2 py-1 bg-red-500 text-white rounded-full text-sm">Inactive</span>';
                }
            },
            {
                data: null,
                name: "actions",
                title: "Actions",
                width: "200px",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (type === "display") {
                        let buttons = "";

                        // Edit button - show for both active and inactive
                        buttons += `<button class="edit-movie bg-blue-500 text-white px-2 py-1 rounded mr-2" data-id="${row.movie_id}">
                            <i class="fas fa-edit"></i> Edit
                        </button>`;

                        // Show different buttons based on active status
                        if (row.active == 1) {
                            buttons += `<button class="delete-movie bg-red-500 text-white px-2 py-1 rounded" data-id="${row.movie_id}">
                                <i class="fas fa-trash"></i> Deactivate
                            </button>`;
                        } else {
                            buttons += `<button class="restore-movie bg-green-500 text-white px-2 py-1 rounded" data-id="${row.movie_id}">
                                <i class="fas fa-undo"></i> Restore
                            </button>`;
                        }

                        return buttons;
                    }
                    return "";
                },
            },
        ],
        // columnDefs: [
        //     {
        //         targets: '_all',
        //         className: 'text-left'
        //     },
        //     {
        //         targets: [6, 7], // Status and Actions columns
        //         className: 'text-center'
        //     }
        // ],
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        language: {
            processing:
                '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
        },
        drawCallback: function () {
            // Add tooltips to description cells if needed
            $(".description-cell").each(function () {
                if (this.scrollWidth > this.offsetWidth) {
                    $(this).attr("title", $(this).text());
                }
            });
        },
    });

    // Add custom styling
    $("head").append(`
        <style>
            .dataTables_wrapper .dataTables_length {
                margin-bottom: 15px;
            }
            .dataTables_wrapper .dataTables_filter {
                margin-bottom: 15px;
            }
            #movieTable {
                width: 100% !important;
            }
            #movieTable th {
                background-color: #f9fafb;
                color: #6b7280;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                padding: 12px 24px;
                border-bottom: 1px solid #e5e7eb;
            }
            #movieTable td {
                padding: 12px 24px;
                border-bottom: 1px solid #e5e7eb;
            }
            .description-cell {
                max-width: 300px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            #movieTable .btn {
                padding: 0.25rem 0.5rem;
                margin: 0 2px;
            }
            .dataTables_scrollBody {
                min-height: 400px;
            }
            /* Custom header styling */
            .dt-head-center {
                text-align: center !important;
            }
            table.dataTable thead th {
                position: relative;
                text-align: left;
                vertical-align: middle;
                padding: 12px 24px;
                border-bottom: 2px solid #e5e7eb;
            }
            table.dataTable thead th.sorting:after,
            table.dataTable thead th.sorting_asc:after,
            table.dataTable thead th.sorting_desc:after {
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                opacity: 0.5;
            }
        </style>
    `);
}

// Handle filter change
document.getElementById("filter").addEventListener("change", function () {
    initializeMovieTable(this.value);
});

// Handle restore movie
$(document).on("click", ".restore-movie", function () {
    const movieId = $(this).data("id");

    Swal.fire({
        title: "Restore Movie?",
        text: "This will reactivate the movie!",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, restore it!",
    }).then((result) => {
        if (result.isConfirmed) {
            restoreMovieStatus(movieId);
        }
    });
});

// Function to restore movie status
async function restoreMovieStatus(movieId) {
    try {
        const response = await fetch(
            `${baseUrl()}/MoviesManagement/restore/${movieId}`,
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
            if (movieTable) {
                movieTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire(
            "Error!",
            error.message || "Failed to restore movie",
            "error"
        );
    }
}

// Handle delete movie
$(document).on("click", ".delete-movie", function () {
    const movieId = $(this).data("id");

    Swal.fire({
        title: "Are you sure?",
        text: "This movie will be deactivated!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, deactivate it!",
    }).then((result) => {
        if (result.isConfirmed) {
            updateMovieStatus(movieId);
        }
    });
});

// Function to update movie status
async function updateMovieStatus(movieId) {
    try {
        const response = await fetch(
            `${baseUrl()}/MoviesManagement/updateStatus/${movieId}`,
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
            if (movieTable) {
                movieTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error("Error:", error);
        Swal.fire(
            "Error!",
            error.message || "Failed to update movie status",
            "error"
        );
    }
}

// Modal Functions
function openModal() {
    const modal = document.getElementById("addMovieModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeModal() {
    const modal = document.getElementById("addMovieModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Initialize form submission
document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("addMovieForm")
        ?.addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch(
                    baseUrl() + "/MoviesManagement/create",
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
                        text: data.message || "Movie added successfully.",
                    });

                    this.reset();
                    closeModal();

                    // Refresh the table after adding a movie
                    if (movieTable) {
                        movieTable.ajax.reload(null, false);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: data.message || "Failed to add Movie.",
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
    const modal = document.getElementById("editMovieModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeEditModal() {
    const modal = document.getElementById("editMovieModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Edit Movie Form
document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("editMovieForm")
        ?.addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const movieId = formData.get("movie_id");

            try {
                const response = await fetch(
                    `${baseUrl()}/MoviesManagement/update/${movieId}`,
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
                        text: data.message || "Movie updated successfully.",
                    });

                    closeEditModal();

                    if (movieTable) {
                        movieTable.ajax.reload(null, false);
                    }
                } else {
                    let errorMessage = data.message || "Failed to update Movie.";
                    if (data.errors) {
                        errorMessage = Object.values(data.errors)
                            .flat()
                            .join("\n");
                    }
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: errorMessage,
                    });
                }
            } catch (error) {
                console.error("Fetch Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Unexpected Error",
                    text: "Network error or server is not responding. Please try again.",
                });
            }
        });
});

// Handle edit button click
$(document).on("click", ".edit-movie", function () {
    const movieId = $(this).data("id");
    const row = movieTable.row($(this).closest("tr")).data();
    console.log("Edit movie clicked. Movie ID:", movieId);
    console.log("Row data:", row);

    // Populate the edit form
    document.getElementById("edit_movie_id").value = movieId;
    document.getElementById("edit_title").value = row.title;
    document.getElementById("edit_genre").value = row.genre;
    document.getElementById("edit_duration").value = row.duration;
    document.getElementById("edit_description").value = row.description;
    document.getElementById("edit_rating").value = row.rating;

    // Open the edit modal
    openEditModal();
});
