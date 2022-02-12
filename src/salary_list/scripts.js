$(function() {
    0;

    //here we save the opened inner_table rows in array
    //so when user filters date, it auto opens them
    var openedTables = new Set();

    // load_daterangepicker()
    load_add_checkins();

    /**
     * Bejme inicializimin e tabeles kryesore
     */
    var tbl1 = $('#checkins_list').DataTable({
        processing: true, serverSide: true, ordering: true, pageLength: 10, lengthMenu: [5, 10, 20], ajax: {
            data: function(data) {
                data.action = 'load_table';
                //Ne momentin qe bejme load tabelen, backendi merr diten e sotme minus 30 dite

                var date_s = $('.daterange');
                var startDate = date_s
                    .data('daterangepicker')
                    .startDate.format('YYYY-MM-DD');
                var endDate = date_s
                    .data('daterangepicker')
                    .endDate.format('YYYY-MM-DD');
                data.startDate = startDate;
                data.endDate = endDate;
            }, url: 'ajax.php', type: 'post'
        }, columns: [{
            class: 'details-control1',
            data: null,
            defaultContent: '<i class="fas fa-plus-circle fa-lg text-success" style="font-size:25px" aria-hidden="true"></i>'
        }, {
            data: 'first_name'
        }, {
            data: 'nr_dates', orderable: false
        }, {
            data: 'normal_hours', orderable: false
        }, {
            data: 'normal_salary', orderable: false
        }, {
            data: 'overtime', orderable: false
        }, {
            data: 'overtime_salary', orderable: false
        }, {
            data: 'total_hours_in', orderable: false
        }, {
            data: 'salary_hour', orderable: false
        }, {
            data: 'salary', orderable: false
        }]
    });

    var search_s = $('div.dataTables_filter input');
    search_s.unbind();

    search_s.on('keyup', delay(function(event) {
        openedTables.clear();
        tbl1.search(this.value).draw();
    }, 500));


    /**
     * Clears opened tables array which auto showed all previously opened tables
     */
    tbl1.on('page.dt', function() {
        openedTables.clear();
    });

    $('#applyFilter').on('click', function() {
        tbl1.draw();
        setTimeout(function() {
            for (let item of openedTables.values()) $('.details-control1').eq(item).trigger('click');
        }, 200);
    });

    $('#checkins_list tbody').on('click', 'tr td.details-control1', function(event) {
        var tr = $(this).closest('tr');
        var row = tbl1.row(tr);
        var index = parseInt(row[0]) + 1;

        if (row.child.isShown()) {
            tr.removeClass('details');
            row.child.hide();
            //Ndryshojme ikonen kur tabela mbyllet
            tr.find('.fas').attr('class', 'fas fa-plus-circle fa-lg text-success');
            openedTables.delete(index + 1);
        } else {
            openedTables.add(index + 1);

            tr.addClass('details');

            row.child(format_tbl_html(2)).show();
            initialize_table(row.data().row_details, 2);

            tr.find('.fas').attr('class', 'fas fa-minus-circle fa-lg text-danger');
        }
    });


    function format_tbl_html(i) {
        /**
         * Bejme draw tabelen e meposhte
         */
        return `
                <table class='innerTable tableclass${i} display' style='width:100%'>
                   <thead>
                        <tr>
                          <th></th>
                            <th class='text-right'>Date</th>
                            <th class='text-right'>Normal Hours</th>
                            <th class='text-right'>Normal Salary</th>
                            <th class='text-right'>Overtime Hours</th>
                            <th class='text-right'>Overtime Salary</th>
                            <th class='text-right'>Total Hours</th>
                            <th class='text-right'>Pay/Hour</th>
                            <th class='text-right'>Salary/week</th>
                        </tr>
                    </thead>
                </table>
`;
    }

    /**
     * Inicializojme tabelen e dyte
     */
    function initialize_table(data, i) {
        //Ruajme te dhena me id-ne e userit si key

        if (`tableclass${i}` === `tableclass4`) {

            var bool = false;

        } else {
             bool=true;
        }

        //inicializojme tabelen dytesore
        var inner_table = $(`.tableclass${i}`).DataTable({
            pageLength: 5,
            lengthMenu: [5, 20, 50, 75, 100],
            retrieve:true,
            paging:bool,
            searching: bool,
            info:bool,
            data: data, columns:
                [{
                    className: `details-control${i}`,
                    orderable: false,
                    data: null,
                    width: '5%',
                    defaultContent: '<i class="fas fa-plus-circle fa-lg text-dark" style="font-size:25px" aria-hidden="true"></i>'
                },
               {
                className: 'dt-body-right', data: 'date'
            }, {
                className: 'dt-body-right', data: 'normal_hours'
            }, {
                className: 'dt-body-right', data: 'normal_salary'
            }, {
                className: 'dt-body-right', data: 'overtime'
            }, {
                className: 'dt-body-right', data: 'overtime_salary'
            }, {
                className: 'dt-body-right', data: 'total_hours'
            }, {
                className: 'dt-body-right', data: 'salary_hour'
            }, {
                className: 'dt-body-right', data: 'salary'
            }]
        });

           if (`tableclass${i}`===`tableclass4`){
               inner_table.column(0).visible(false);
           }
           else {
               second_table_details_control(inner_table, i);
           }
    }


    function second_table_details_control(inner_table,i) {
        $(`.tableclass${i} tbody`).on('click', `td.details-control${i}`, function(event) {
            console.log(i);
            var tr = $(this).closest('tr');

            var row = inner_table.row(tr);

            if (row.child.isShown()) {
                // Nese rresht eshte i hapur, e mbyllim
                row.child.hide();

                //Ndryshim ikone ne hide
                tr.find('.fa-minus-circle').attr('class', 'fas fa-plus-circle fa-lg text-dark');
            } else {
                // initialize_table_3(user_id);
                row.child(format_tbl_html(i+1)).show();

                initialize_table(row.data().row_details,i+1);

                //Ndryshim ikone ne show
                tr.find('.fa-plus-circle').attr('class', 'fas fa-minus-circle fa-lg text-dark');
            }
        });
    }


    function load_add_checkins() {
        $('#addCheckin').on('click', function(event) {
            event.preventDefault();

            /**
             * Marrim te dhenat nga useri
             */
            var email = $('#email').val();
            var checkin = $('#checkin').val();
            var checkout = $('#checkout').val();
            var date_s = $('#daterange');

            var checkin_date = date_s
                .data('daterangepicker')
                .startDate.format('YYYY-MM-DD');
            var checkout_date = date_s
                .data('daterangepicker')
                .endDate.format('YYYY-MM-DD');

            //I cojme ne backend
            $.ajax({
                url: 'ajax.php', method: 'POST', data: {
                    action: 'add_checking',
                    email: email,
                    checkin: checkin,
                    checkout: checkout,
                    checkin_date: checkin_date,
                    checkout_date: checkout_date
                }, cache: false, beforeSend: function(xhr) {
                    $('button').attr('disabled', 'disabled');
                }, success: function(response) {
                    response = JSON.parse(response);
                    if (response.status != 200) {
                        Swal.fire('Error!', response['message'], 'error');
                    } else {
                        Swal.fire('Success!', response['message'], 'success');
                    }
                    setTimeout(function() {
                        $('button').prop('disabled', false);
                    }, 1500);
                }
            });
        });
    }
});
