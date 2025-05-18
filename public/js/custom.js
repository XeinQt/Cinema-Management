let table = new DataTable("#mallsTable ");

function baseUrl() {
    return location.protocol + "//" + location.host + "";
}
new DataTable("#mallsDatatables", {
    ajax: baseUrl() + "/MallsManagement/DataTables",
    processing: true,
    serverSide: true,
    columnDefs: [
        { targets: [0, 1, 2, 3], visible: true }, 
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
    ],
});
