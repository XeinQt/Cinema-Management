let screeningTable;
let currentFilter = "";

const dropdownCache = {
    cinemas: null,
    movies: null
};

function initializeScreeningTable(filter = "") {
    currentFilter = filter;

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#screeningTable')) {
        $('#screeningTable').DataTable().destroy();
    }

    screeningTable = $('#screeningTable').DataTable({
        ajax: {
            url: baseUrl() + "/ScreeningsManagement/DataTables",
            type: 'GET',
            data: function(d) {
                d.filter = filter;
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load screening data. Please try again.'
                });
            }
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        columns: [
            {
                data: "screening_id",
                name: "screening_id",
                title: "ID"
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
                data: null,
                title: "Actions",
                className: "items-start",
                orderable: false,
                render: function(data, type, row) {
                    let buttons = '<div class="flex space-x-2">';
                    
                    // Edit button - show for both active and inactive
                    buttons += `<button class="edit-screening inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded" data-id="${row.screening_id}">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>`;

                    // Show different buttons based on active status
                    if (row.active == 1) {
                        buttons += `<button class="delete-screening inline-flex items-center bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded" data-id="${row.screening_id}">
                            <i class="fas fa-trash mr-1"></i> Deactivate
                        </button>`;
                    } else {
                        buttons += `<button class="restore-screening inline-flex items-center bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded" data-id="${row.screening_id}">
                            <i class="fas fa-undo mr-1"></i> Restore
                        </button>`;
                    }
                    
                    buttons += '</div>';
                    return buttons;
                }
            }
        ],
        columnDefs: [
            {
                targets: 0, // ID column
                width: '50px',
                className: 'text-left'
            },
            {
                targets: [1, 2], // Cinema and Movie columns
                width: '200px',
                className: 'text-left'
            },
            {
                targets: 3, // Screening Time column
                width: '150px',
                className: 'text-left'
            },
            {
                targets: 4, // Status column
                width: '100px',
                className: 'text-left'
            },
            {
                targets: -1, // Actions column
                width: '150px',
                className: 'text-left flex justify-start'
            }
        ],
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        autoWidth: false,
        responsive: true,
        pageLength: 10,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
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
    const screeningId = $(this).data('id');
    
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
            Swal.fire(
                'Deactivated!',
                data.message,
                'success'
            );
            // Reload the DataTable to reflect the changes
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

// Initialize form submission
document.addEventListener('DOMContentLoaded', function() {
    initializeScreeningTable();
    
    document.getElementById("addScreeningForm")?.addEventListener("submit", async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        
        // Format the datetime-local value to proper MySQL datetime format
        const screeningTime = formData.get('time');
        if (screeningTime) {
            const date = new Date(screeningTime);
            formData.set('time', date.toISOString().slice(0, 19).replace('T', ' '));
        }

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
                clearDropdownCache();
                
                // Refresh the table after adding a screening
                if (screeningTable) {
                    screeningTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "Failed to add screening.",
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

    // Add event listener for edit form
    document.getElementById("editScreeningForm")?.addEventListener("submit", async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const screeningId = formData.get('screening_id');
        
        // Format the datetime-local value
        const screeningTime = formData.get('time');
        if (screeningTime) {
            const date = new Date(screeningTime);
            formData.set('time', date.toISOString().slice(0, 19).replace('T', ' '));
        }

        try {
            const response = await fetch(`${baseUrl()}/ScreeningsManagement/update/${screeningId}`, {
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
                    title: "Error",
                    text: data.message || "Failed to update screening.",
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

// Function to populate cinema and movie dropdowns
async function populateDropdowns() {
    try {
        const cinemaSelect = document.getElementById('cinema_select');
        const movieSelect = document.getElementById('movie_select');
        
        const cinemaFragment = document.createDocumentFragment();
        const movieFragment = document.createDocumentFragment();

        const defaultCinemaOption = new Option('Select Cinema', '');
        const defaultMovieOption = new Option('Select Movie', '');
        cinemaFragment.appendChild(defaultCinemaOption);
        movieFragment.appendChild(defaultMovieOption);

        const [cinemasData, moviesData] = await Promise.all([
            dropdownCache.cinemas || fetch(baseUrl() + '/CinemasManagement/DataTables').then(r => r.json()),
            dropdownCache.movies || fetch(baseUrl() + '/MoviesManagement/DataTables').then(r => r.json())
        ]);

        if (!dropdownCache.cinemas) dropdownCache.cinemas = cinemasData;
        if (!dropdownCache.movies) dropdownCache.movies = moviesData;

        // Filter for active cinemas only
        const activeCinemas = cinemasData.data.filter(cinema => cinema.active === 1);
        activeCinemas.forEach(cinema => {
            const option = new Option(cinema.name, cinema.cinema_id);
            cinemaFragment.appendChild(option);
        });

        // Filter for active movies only
        const activeMovies = moviesData.data.filter(movie => movie.active === 1);
        activeMovies.forEach(movie => {
            const option = new Option(movie.title, movie.movie_id);
            movieFragment.appendChild(option);
        });

        cinemaSelect.innerHTML = '';
        movieSelect.innerHTML = '';
        cinemaSelect.appendChild(cinemaFragment);
        movieSelect.appendChild(movieFragment);

    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load cinema and movie data'
        });
    }
}

function clearDropdownCache() {
    dropdownCache.cinemas = null;
    dropdownCache.movies = null;
}

function openEditModal() {
    const modal = document.getElementById("editScreeningModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    populateEditDropdowns();
}

function closeEditModal() {
    const modal = document.getElementById("editScreeningModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Function to populate edit form dropdowns
async function populateEditDropdowns() {
    try {
        const cinemaSelect = document.getElementById('edit_cinema_select');
        const movieSelect = document.getElementById('edit_movie_select');
        
        const cinemaFragment = document.createDocumentFragment();
        const movieFragment = document.createDocumentFragment();

        const defaultCinemaOption = new Option('Select Cinema', '');
        const defaultMovieOption = new Option('Select Movie', '');
        cinemaFragment.appendChild(defaultCinemaOption);
        movieFragment.appendChild(defaultMovieOption);

        const [cinemasData, moviesData] = await Promise.all([
            dropdownCache.cinemas || fetch(baseUrl() + '/CinemasManagement/DataTables').then(r => r.json()),
            dropdownCache.movies || fetch(baseUrl() + '/MoviesManagement/DataTables').then(r => r.json())
        ]);

        if (!dropdownCache.cinemas) dropdownCache.cinemas = cinemasData;
        if (!dropdownCache.movies) dropdownCache.movies = moviesData;

        // Filter for active cinemas only
        const activeCinemas = cinemasData.data.filter(cinema => cinema.active === 1);
        activeCinemas.forEach(cinema => {
            const option = new Option(cinema.name, cinema.cinema_id);
            cinemaFragment.appendChild(option);
        });

        // Filter for active movies only
        const activeMovies = moviesData.data.filter(movie => movie.active === 1);
        activeMovies.forEach(movie => {
            const option = new Option(movie.title, movie.movie_id);
            movieFragment.appendChild(option);
        });

        cinemaSelect.innerHTML = '';
        movieSelect.innerHTML = '';
        cinemaSelect.appendChild(cinemaFragment);
        movieSelect.appendChild(movieFragment);

    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load dropdown data'
        });
    }
}

// Handle edit button click
$(document).on('click', '.edit-screening', async function() {
    const screeningId = $(this).data('id');
    const row = screeningTable.row($(this).closest('tr')).data();
    
    document.getElementById('edit_screening_id').value = screeningId;
    
    // Wait for dropdowns to be populated
    await populateEditDropdowns();
    
    // Set the values
    document.getElementById('edit_cinema_select').value = row.cinema_id;
    document.getElementById('edit_movie_select').value = row.movie_id;
    
    // Format the date for datetime-local input
    const screeningTime = new Date(row.screening_time);
    const formattedTime = screeningTime.toISOString().slice(0, 16); // Format: YYYY-MM-DDThh:mm
    document.getElementById('edit_screening_time').value = formattedTime;
    
    openEditModal();
});

// Handle restore screening
$(document).on('click', '.restore-screening', function() {
    const screeningId = $(this).data('id');
    
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
            restoreScreening(screeningId);
        }
    });
});

// Function to restore screening
async function restoreScreening(screeningId) {
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
            Swal.fire(
                'Restored!',
                data.message,
                'success'
            );
            // Reload the DataTable to reflect the changes
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