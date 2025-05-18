let table = new DataTable("#mallsTable ");

function baseUrl() {
    return location.protocol + "//" + location.host + "";
}
//malls table
new DataTable("#mallsDatatables", {
    ajax: baseUrl() + "/MallsManagement/DataTables",
    processing: true,
    serverSide: true,
    columnDefs: [
        { targets: [0, 1, 2, 3, 4], visible: true },
        { targets: "_all", visivle: false },
    ],
    columns: [
        {
            data: "mall_id",
            name: "mall_id",
            title: "Mall ID",
        },
        {
            data: "name",
            name: "name",
            title: "Name",
        },
        {
            data: "location",
            name: "location",
            title: "Location",
        },
        {
            data: "description",
            name: "description",
            title: "Description",
        },
        {
            data: "active",
            name: "active",
            title: "Active",
        },
    ],
});

//cinemnas
new DataTable("#cinemaTable", {
    ajax: baseUrl() + "/CinemasManagement/DataTables",
    processing: true,
    serverSide: true,
    columnDefs: [
        { targets: [0, 1, 2, 3, 4], visible: true },
        { targets: "_all", visivle: false },
    ],
    columns: [
        {
            data: "cinema_id",
            name: "cinema_id",
            title: "Cinema ID",
        },
        {
            data: "mall_id",
            name: "mall_id",
            title: "Mall Id",
        },
        {
            data: "manager_id",
            name: "manager_id",
            title: "Manager Id ",
        },
        {
            data: "name",
            name: "name",
            title: "Name",
        },
        {
            data: "active",
            name: "active",
            title: "Active",
        },
    ],
});

//manager tables
new DataTable("#managerTable", {
    ajax: baseUrl() + "/ManagersManagement/DataTables",
    processing: true,
    serverSide: true,
    columnDefs: [
        { targets: [0, 1, 2, 3, 4, 5], visible: true },
        { targets: "_all", visivle: false },
    ],
    columns: [
        {
            data: "manager_id",
            name: "manager_id",
            title: "Manager ID",
        },
        {
            data: "first_name",
            name: "first_name",
            title: "First Name",
        },
        {
            data: "last_name",
            name: "last_name",
            title: "Last Name",
        },
        {
            data: "email",
            name: "email",
            title: "Email",
        },
        {
            data: "phonenumber",
            name: "phonenumber",
            title: "Phone No.",
        },
        {
            data: "active",
            name: "active",
            title: "Active",
        },
    ],
});

//customer tables
new DataTable("#customerTable", {
    ajax: baseUrl() + "/CustomersManagement/DataTables",
    processing: true,
    serverSide: true,
    columnDefs: [
        { targets: [0, 1, 2, 3, 4, 5], visible: true },
        { targets: "_all", visivle: false },
    ],
    columns: [
        {
            data: "customer_id",
            name: "customer_id",
            title: "Customer ID",
        },
        {
            data: "first_name",
            name: "first_name",
            title: "First Name",
        },
        {
            data: "last_name",
            name: "last_name",
            title: "Last Name",
        },
        {
            data: "email",
            name: "email",
            title: "Email",
        },
        {
            data: "phonenumber",
            name: "phonenumber",
            title: "Phone No.",
        },
        {
            data: "active",
            name: "active",
            title: "Active",
        },
    ],
});

//movies tables
new DataTable("#moviesTable", {
    ajax: baseUrl() + "/MoviesManagement/DataTables",
    processing: true,
    serverSide: true,
    columnDefs: [
        { targets: [0, 1, 2, 3, 4, 5, 6], visible: true },
        { targets: "_all", visivle: false },
    ],
    columns: [
        {
            data: "movie_id",
            name: "movie_id",
            title: "Movies ID",
        },
        {
            data: "title",
            name: "title",
            title: "Title",
        },
        {
            data: "genre",
            name: "genre",
            title: "Genre",
        },
        {
            data: "duration",
            name: "duration",
            title: "Duration",
        },
        {
            data: "description",
            name: "description",
            title: "Description",
        },
        {
            data: "rating",
            name: "rating",
            title: "Rating",
        },
        {
            data: "active",
            name: "active",
            title: "Active",
        },
    ],
});

//screening tables
new DataTable("#screeningTable", {
    ajax: baseUrl() + "/ScreeningsManagement/DataTables",
    processing: true,
    serverSide: true,
    columnDefs: [
        { targets: [0, 1, 2, 3, 4], visible: true },
        { targets: "_all", visivle: false },
    ],
    columns: [
        {
            data: "screening_id",
            name: "screening_id",
            title: "Screening ID",
        },
        {
            data: "cinema_id",
            name: "cinema_id",
            title: "Cinema ID",
        },
        {
            data: "movie_id",
            name: "movie_id",
            title: "Movie ID",
        },
        {
            data: "screening_time",
            name: "screening_time",
            title: "Screening Time",
        },
        {
            data: "active",
            name: "active",
            title: "Active",
        },
    ],
});

//booking tables
new DataTable("#bookingTable", {
    ajax: baseUrl() + "/BookingsManagement/DataTables",
    processing: true,
    serverSide: true,
    columnDefs: [
        { targets: [0, 1, 2, 3, 4, 5], visible: true },
        { targets: "_all", visivle: false },
    ],
    columns: [
        {
            data: "booking_id",
            name: "booking_id",
            title: "Booking ID",
        },
        {
            data: "customer_id",
            name: "customer_id",
            title: "Customer ID",
        },
        {
            data: "screening_id",
            name: "screening_id",
            title: "Screening ID",
        },
        {
            data: "set_number",
            name: "set_number",
            title: "Set Number",
        },
        {
            data: "status",
            name: "status",
            title: "Status",
        },
        {
            data: "active",
            name: "active",
            title: "Active",
        },
    ],
});
