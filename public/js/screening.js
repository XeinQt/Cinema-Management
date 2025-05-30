let screeningTable;
let currentFilter = "";

const dropdownCache = {
    cinemas: null,
    movies: null
};

function initializeScreeningTable(filter = "") {
    currentFilter = filter;

    if ($.fn.DataTable.isDataTable('#screeningTable')) {
        $('#screeningTable').DataTable().destroy();
    }

    screeningTable = $('#screeningTable').DataTable({
        ajax: {
            url: baseUrl() + "/ScreeningsManagement/DataTables",
            data: function(d) {
                d.filter = filter;
            }
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        columns: [
            {
                data: "screening_id",
                name: "screening_id",
                title: "ID",
                width: "50px"
            },
            {
                data: "cinema_name",
                name: "cinema_name",
                title: "Cinema"
            },
            {
                data: "movie_title",
                name: "movie_title",
                title: "Movie"
            },
            {
                data: "screening_time",
                name: "screening_time",
                title: "Screening Time",
                render: function(data) {
                    const date = new Date(data);
                    return date.toLocaleString();
                }
            },
            {
                data: "active",
                name: "active",
                title: "Status",
                render: function(data) {
                    return data == 1
                        ? '<span class="px-2 py-1 bg-green-500 text-white rounded-full text-sm">Active</span>'
                        : '<span class="px-2 py-1 bg-red-500 text-white rounded-full text-sm">Inactive</span>';
                }
            },
            {
                data: "action",
                name: "action",
                title: "Actions",
                orderable: false,
                searchable: false,
                width: "100px"
            }
        ],
        order: [[0, "desc"]],
        drawCallback: function() {
            document.getElementById("filter").value = currentFilter;
        }
    });

    // Add custom styling
    $('head').append(`
        <style>
            .dataTables_wrapper .dataTables_length {
                margin-bottom: 15px;
            }
            .dataTables_wrapper .dataTables_filter {
                margin-bottom: 15px;
            }
            #screeningTable {
                width: 100% !important;
            }
            #screeningTable th, #screeningTable td {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            #screeningTable .btn {
                padding: 0.25rem 0.5rem;
                margin: 0 2px;
            }
            .dataTables_scrollBody {
                min-height: 400px;
            }
            .spinner-border {
                display: inline-block;
                width: 2rem;
                height: 2rem;
                vertical-align: text-bottom;
                border: 0.25em solid currentColor;
                border-right-color: transparent;
                border-radius: 50%;
                animation: spinner-border .75s linear infinite;
            }
            @keyframes spinner-border {
                to { transform: rotate(360deg); }
            }
        </style>
    `);
}

// Handle filter change
document.getElementById("filter").addEventListener("change", function() {
    initializeScreeningTable(this.value);
});

// Handle delete screening
$(document).on('click', '.delete-screening', function() {
    const screeningId = $(this).attr('data-id');
    if (!screeningId) {
        console.error("Screening ID not found");
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: "This screening will be deactivated!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, deactivate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateScreeningStatus(screeningId);
        }
    });
});

// Function to update screening status
async function updateScreeningStatus(screeningId) {
    try {
        const response = await fetch(`${baseUrl()}/ScreeningsManagement/updateStatus/${screeningId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire('Deactivated!', data.message, 'success');
            if (screeningTable) {
                screeningTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            error.message || 'Failed to update screening status',
            'error'
        );
    }
}

// Modal Functions
function openModal() {
    const modal = document.getElementById("addScreeningModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    populateDropdowns();
}

function closeModal() {
    const modal = document.getElementById("addScreeningModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

function openEditModal() {
    const modal = document.getElementById("editScreeningModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeEditModal() {
    const modal = document.getElementById("editScreeningModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

async function populateDropdowns() {
    try {
        // Fetch cinemas
        const cinemasResponse = await fetch(baseUrl() + "/CinemasManagement/DataTables");
        const cinemasData = await cinemasResponse.json();
        const cinemaSelect = document.getElementById("cinema_select");

        // Clear existing options
        cinemaSelect.innerHTML = '<option value="">Select Cinema</option>';

        // Filter for active cinemas only
        const activeCinemas = cinemasData.data.filter(cinema => cinema.active === 1);

        activeCinemas.forEach(cinema => {
            const option = document.createElement("option");
            option.value = cinema.cinema_id;
            option.textContent = cinema.name;
            cinemaSelect.appendChild(option);
        });

        // Fetch movies
        const moviesResponse = await fetch(baseUrl() + "/MoviesManagement/DataTables");
        const moviesData = await moviesResponse.json();
        const movieSelect = document.getElementById("movie_select");

        // Clear existing options
        movieSelect.innerHTML = '<option value="">Select Movie</option>';

        // Filter for active movies only
        const activeMovies = moviesData.data.filter(movie => movie.active === 1);

        activeMovies.forEach(movie => {
            const option = document.createElement("option");
            option.value = movie.movie_id;
            option.textContent = movie.title;
            movieSelect.appendChild(option);
        });

    } catch (error) {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to load dropdown data"
        });
    }
}

async function populateEditDropdowns() {
    try {
        // Fetch cinemas
        const cinemasResponse = await fetch(baseUrl() + "/CinemasManagement/DataTables");
        const cinemasData = await cinemasResponse.json();
        const cinemaSelect = document.getElementById("edit_cinema_select");

        // Clear existing options
        cinemaSelect.innerHTML = '<option value="">Select Cinema</option>';

        // Filter for active cinemas only
        const activeCinemas = cinemasData.data.filter(cinema => cinema.active === 1);

        activeCinemas.forEach(cinema => {
            const option = document.createElement("option");
            option.value = cinema.cinema_id;
            option.textContent = cinema.name;
            cinemaSelect.appendChild(option);
        });

        // Fetch movies
        const moviesResponse = await fetch(baseUrl() + "/MoviesManagement/DataTables");
        const moviesData = await moviesResponse.json();
        const movieSelect = document.getElementById("edit_movie_select");

        // Clear existing options
        movieSelect.innerHTML = '<option value="">Select Movie</option>';

        // Filter for active movies only
        const activeMovies = moviesData.data.filter(movie => movie.active === 1);

        activeMovies.forEach(movie => {
            const option = document.createElement("option");
            option.value = movie.movie_id;
            option.textContent = movie.title;
            movieSelect.appendChild(option);
        });

    } catch (error) {
        console.error("Error:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to load dropdown data"
        });
    }
}

// Initialize form submission
document.addEventListener("DOMContentLoaded", function() {
    // Initialize the table
    initializeScreeningTable();

    // Add Screening Form Submission
    document.getElementById("addScreeningForm")?.addEventListener("submit", async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch(baseUrl() + "/ScreeningsManagement/create", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: data.message || "Screening added successfully.",
                });

                this.reset();
                closeModal();

                if (screeningTable) {
                    screeningTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to add Screening.",
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

    // Edit Screening Form Submission
    document.getElementById("editScreeningForm")?.addEventListener("submit", async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const screeningId = formData.get("screening_id");

        // Get the original row data
        const originalRow = screeningTable.row($('.edit-screening[data-id="' + screeningId + '"]').closest('tr')).data();
        
        // Check if any data has changed
        const hasChanged = 
            originalRow.cinema_name !== $('#edit_cinema_select option:selected').text() ||
            originalRow.movie_title !== $('#edit_movie_select option:selected').text() ||
            originalRow.screening_time !== formData.get("time");

        if (!hasChanged) {
            Swal.fire({
                icon: "info",
                title: "No Changes",
                text: "No changes were made to the screening."
            });
            closeEditModal();
            return;
        }

        try {
            const response = await fetch(baseUrl() + `/ScreeningsManagement/update/${screeningId}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: data.message || "Screening updated successfully.",
                });

                closeEditModal();

                if (screeningTable) {
                    screeningTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to update Screening.",
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
});

// Handle edit screening
$(document).on('click', '.edit-screening', function() {
    const screeningId = $(this).attr('data-id');
    if (!screeningId) {
        console.error("Screening ID not found");
        return;
    }

    const row = screeningTable.row($(this).closest('tr')).data();

    // Populate the edit form
    document.getElementById('edit_screening_id').value = screeningId;
    document.getElementById('edit_screening_time').value = row.screening_time;

    // Populate cinema and movie dropdowns
    populateEditDropdowns().then(() => {
        // Set selected values after dropdowns are populated
        const cinemaSelect = document.getElementById('edit_cinema_select');
        const movieSelect = document.getElementById('edit_movie_select');

        // Find and select the cinema option
        Array.from(cinemaSelect.options).forEach((option) => {
            if (option.textContent === row.cinema_name) {
                option.selected = true;
            }
        });

        // Find and select the movie option
        Array.from(movieSelect.options).forEach((option) => {
            if (option.textContent === row.movie_title) {
                option.selected = true;
            }
        });
    });

    // Open the edit modal
    openEditModal();
});

// Handle restore screening
$(document).on('click', '.restore-screening', function() {
    const screeningId = $(this).attr('data-id');
    if (!screeningId) {
        console.error("Screening ID not found");
        return;
    }

    Swal.fire({
        title: 'Restore Screening?',
        text: "This will reactivate the screening!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, restore it!'
    }).then((result) => {
        if (result.isConfirmed) {
            restoreScreeningStatus(screeningId);
        }
    });
});

// Function to restore screening status
async function restoreScreeningStatus(screeningId) {
    try {
        const response = await fetch(`${baseUrl()}/ScreeningsManagement/restore/${screeningId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire('Restored!', data.message, 'success');
            if (screeningTable) {
                screeningTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            error.message || 'Failed to restore screening',
            'error'
        );
    }
}