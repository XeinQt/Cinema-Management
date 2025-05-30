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
        ajax: baseUrl() + "/ScreeningsManagement/DataTables",
        processing: true,
        serverSide: true,
        columns: [
            {
                data: "screening_id",
                name: "screening_id",
                title: "Screening ID"
            },
            {
                data: "cinema_id",
                name: "cinema_id",
                title: "Cinema ID"
            },
            {
                data: "movie_id",
                name: "movie_id",
                title: "Movie ID"
            },
            {
                data: "screening_time",
                name: "screening_time",
                title: "Screening Time"
            },
            {
                data: null,
                title: "Actions",
                orderable: false,
                render: function(data, type, row) {
                    return getActionButtons(row.screening_id, 'screening');
                }
            }
        ]
    });
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