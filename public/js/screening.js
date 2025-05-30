let screeningTable;

const dropdownCache = {
    cinemas: null,
    movies: null
};

function initializeScreeningTable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#screeningTable')) {
        $('#screeningTable').DataTable().destroy();
    }

    screeningTable = $('#screeningTable').DataTable({
        ajax: {
            url: baseUrl() + "/ScreeningsManagement/DataTables",
            type: 'GET',
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
                data: null,
                title: "Actions",
                orderable: false,
                render: function(data, type, row) {
                    return getActionButtons(row.screening_id, 'screening');
                }
            }
        ],
        columnDefs: [
            {
                targets: 0, // ID column
                width: '50px',
                className: 'text-center'
            },
            {
                targets: [1, 2], // Cinema and Movie columns
                width: '200px'
            },
            {
                targets: 3, // Screening Time column
                width: '150px',
                className: 'text-center'
            },
            {
                targets: -1, // Actions column
                width: '150px',
                className: 'text-center'
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
    
    document.getElementById("addScreeningForm")?.addEventListener("submit", async function (e) {
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
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Screening added successfully.'
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
                    title: "Oops...",
                    text: data.message || "Failed to add Screening.",
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

        cinemasData.data.forEach(cinema => {
            cinemaFragment.appendChild(new Option(cinema.name, cinema.name));
        });

        moviesData.data.forEach(movie => {
            movieFragment.appendChild(new Option(movie.title, movie.title));
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

        cinemasData.data.forEach(cinema => {
            cinemaFragment.appendChild(new Option(cinema.name, cinema.name));
        });

        moviesData.data.forEach(movie => {
            movieFragment.appendChild(new Option(movie.title, movie.title));
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

// Handle edit button click
$(document).on('click', '.edit-screening', function() {
    const screeningId = $(this).data('id');
    const row = screeningTable.row($(this).closest('tr')).data();
    
    // Set form values
    document.getElementById('edit_screening_id').value = screeningId;
    
    // Format the date for datetime-local input
    const screeningTime = new Date(row.screening_time);
    const formattedTime = screeningTime.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:mm
    document.getElementById('edit_screening_time').value = formattedTime;
    
    // Open modal and populate dropdowns
    openEditModal();
    
    // Set selected values after dropdowns are populated
    const checkDropdowns = setInterval(() => {
        const cinemaSelect = document.getElementById('edit_cinema_select');
        const movieSelect = document.getElementById('edit_movie_select');
        
        if (cinemaSelect.options.length > 1 && movieSelect.options.length > 1) {
            clearInterval(checkDropdowns);
            
            // Find and select the cinema option
            Array.from(cinemaSelect.options).forEach(option => {
                if (option.value === row.cinema_name) {
                    option.selected = true;
                }
            });
            
            // Find and select the movie option
            Array.from(movieSelect.options).forEach(option => {
                if (option.value === row.movie_title) {
                    option.selected = true;
                }
            });
        }
    }, 100);
});

// Handle edit form submission
document.getElementById("editScreeningForm")?.addEventListener("submit", async function(e) {
    e.preventDefault();
    
    const screeningId = document.getElementById('edit_screening_id').value;
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`${baseUrl()}/ScreeningsManagement/update/${screeningId}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json"
            },
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message || 'Screening updated successfully.'
            });

            closeEditModal();
            if (screeningTable) {
                screeningTable.ajax.reload(null, false);
            }
        } else {
            let errorMessage = data.message || "Failed to update Screening.";
            if (data.errors) {
                errorMessage = Object.values(data.errors).flat().join('\n');
            }
            Swal.fire({
                icon: "error",
                title: "Error",
                text: errorMessage
            });
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        Swal.fire({
            icon: "error",
            title: "Unexpected Error",
            text: "Network error or server is not responding. Please try again."
        });
    }
});