let movieTable;
let currentFilter = "";

function initializeMovieTable(filter = "") {
    console.log("Initializing movie table with filter:", filter);
    currentFilter = filter;

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable("#moviesDatatables")) {
        console.log("Destroying existing DataTable");
        $("#moviesDatatables").DataTable().destroy();
    }

    console.log("Creating new DataTable instance");
    console.log("AJAX URL:", baseUrl() + "/MoviesManagement/DataTables");

    movieTable = $("#moviesDatatables").DataTable({
        ajax: {
            url: baseUrl() + "/MoviesManagement/DataTables",
            type: "GET",
            data: function (d) {
                d.filter = filter;
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
        autoWidth: false,
        columns: [
            {
                data: "movie_id",
                name: "movie_id",
                title: "Movie ID",
                width: "10px",
            },
            {
                data: "title",
                name: "title",
                title: "Title",
                width: "50px",
            },
            {
                data: "genre",
                name: "genre",
                title: "Genre",
                width: "50px",
            },
            {
                data: "duration",
                name: "duration",
                title: "Duration (mins)",
                width: "40px",
            },
            {
                data: "rating",
                name: "rating",
                title: "Rating",
                width: "20px",
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
    initializeMovieTable(this.value);
});

// Handle delete movie
$(document).on("click", ".delete-movie", function () {
    const movieId = $(this).attr("data-id");
    if (!movieId) {
        console.error("Movie ID not found");
        return;
    }

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
        console.log("Updating movie status for ID:", movieId); // Debug log
        const url = `${baseUrl()}/MoviesManagement/updateStatus/${movieId}`;
        console.log("Request URL:", url); // Debug log

        const response = await fetch(url, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                Accept: "application/json",
                "Content-Type": "application/json",
            },
        });

        console.log("Response status:", response.status); // Debug log
        const data = await response.json();
        console.log("Response data:", data); // Debug log

        if (data.success) {
            Swal.fire("Deactivated!", data.message, "success");
            if (movieTable) {
                movieTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message || "Failed to update movie status");
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

// Handle restore movie
$(document).on("click", ".restore-movie", function () {
    const movieId = $(this).attr("data-id");
    if (!movieId) {
        console.error("Movie ID not found");
        return;
    }

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

// Handle edit movie
$(document).on("click", ".edit-movie", function () {
    const movieId = $(this).attr("data-id");
    if (!movieId) {
        console.error("Movie ID not found");
        return;
    }

    const row = movieTable.row($(this).closest("tr")).data();
    console.log("Row data:", row); // Debug log

    // Populate the edit form
    document.getElementById("edit_movie_id").value = movieId;
    document.getElementById("edit_title").value = row.title;
    document.getElementById("edit_genre").value = row.genre;
    document.getElementById("edit_duration").value = row.duration;
    console.log("Duration value:", row.duration); // Debug log
    document.getElementById("edit_description").value = row.description;
    document.getElementById("edit_rating").value = row.rating;

    // Open the edit modal
    openEditModal();
});

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

// Initialize form submission
document.addEventListener("DOMContentLoaded", function () {
    // Add Movie Form Submission
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
                console.error("Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Unexpected Error",
                    text: "Something went wrong. Please try again.",
                });
            }
        });

    // Edit Movie Form Submission
    document
        .getElementById("editMovieForm")
        ?.addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const movieId = formData.get("movie_id");

            try {
                const response = await fetch(
                    baseUrl() + `/MoviesManagement/update/${movieId}`,
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
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: data.message || "Failed to update Movie.",
                    });
                }
            } catch (error) {
                console.error("Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Unexpected Error",
                    text: "Something went wrong. Please try again.",
                });
            }
        });

    // Initialize the table
    initializeMovieTable();
});
