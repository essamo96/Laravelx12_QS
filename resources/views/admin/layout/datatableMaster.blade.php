   $(document).ready(function() {
            $("#from_date,#to_date").flatpickr();

             table = $('#kt_table').DataTable({
                responsive: true,
                ordering: false,
                processing: true,
                bLengthChange: true,
                bFilter: false,
                serverSide: true,
                ajax: {
                    url: ajaxUrl,
                    data: function(d) {
                        d.name = $('#generalSearch').val();
                        d.from = $('#from_date').val();
                        d.to = $('#to_date').val();
                        d.holdays_status = $('#holdays_status').val();
                        d.holdays_type = $('#holdays_type').val();
                    }
                },
                columns: columns,
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td:eq(1)').addClass('d-flex align-items-center');
                }
            });
            $('#generalSearch , #from_date , #to_date , #holdays_status ,#holdays_type').on('change keyup', function(e) {
                table.draw();
            });
         
        });