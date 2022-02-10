$(function() {
    0;

    //here we save the opened tbl2 rows in array
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
            data: 'user_id',
            defaultContent: '<i class="fas fa-plus-circle fa-lg text-success" style="font-size:25px" aria-hidden="true"></i>'
        }, {
            data: 'first_name'
        }, {
            data: 'dates', orderable: false
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
            data: 'salary_per_hour', orderable: false
        }, {
            data: 'salary', orderable: false
        }],

        createdRow: function(row, data, dataIndex) {
            /**
             * EDIT ROW DATA
             */
            if (data['show']) {
                $(row).children('td').eq(0).html(`
                <button id='button-${dataIndex + 1}' class='show' style='border:none;background:none;margin-top:8px' class='button-primary' value='${dataIndex + 1}'>
                <i class='fas fa-plus-circle text-success' style='font-size:25px' ></i>
                </button>`);
            }

            if (data['dates']) {
                $(row).children('td').eq(2).html(`<span id='dates-${dataIndex + 1}'></span>`);
            }

            if (data['normal_hours']) {
                $(row).children('td').eq(3).html(`<span id='normalh-${dataIndex + 1}'></span>`);
            }

            if (data['normal_salary']) {
                $(row).children('td').eq(4).html(`<span id='normals-${dataIndex + 1}'></span>`);
            }

            if (data['overtime']) {
                $(row).children('td').eq(5).html(`<span id='overtimeh-${dataIndex + 1}'></span>`);
            }

            if (data['overtime_salary']) {
                $(row).children('td').eq(6).html(`<span id='overtimes-${dataIndex + 1}'></span>`);
            }

            if (data['total_hours_in']) {
                $(row).children('td').eq(7).html(`<span id='totalh-${dataIndex + 1}'></span>`);
            }

            if (data['salary_per_hour']) {
                $(row).children('td').eq(8).html(`<span id='sph-${dataIndex + 1}'></span>`);
            }

            if (data['salary']) {
                $(row).children('td').eq(9).html(`<span id='salary-${dataIndex + 1}'></span>`);
            }
        }, drawCallback: function(settings) {
            window.employee_data = settings.json['checkinsData'];

            setTimeout(function() {
                for (let item of openedTables.values()) $('.details-control1').eq(item).trigger('click');
            }, 200);
        }, initComplete: function(settings, json) {
            /**
             * Start of search delay
             */
            var search_s = $('div.dataTables_filter input');
            search_s.unbind();

            search_s.on('keyup', delay(function(event) {
                openedTables.clear();
                tbl1.search(this.value).draw();
            }, 500));
            /**
             * End of search delay
             */
        }
    });

    load_checkins(tbl1);

    /**
     * Clears opened tables array which auto showed all previously opened tables
     */
    tbl1.on('page.dt', function() {
        openedTables.clear();
    });

    $('#applyFilter').on('click', function() {
        tbl1.draw();
    });

    $('#checkins_list tbody').on('click', 'tr td.details-control1', function(event) {
        var tr = $(this).closest('tr');
        var row = tbl1.row(tr);
        var index = parseInt(row[0]) + 1;

        var rows = tbl1.rows(index - 1).data();
        var user_id = rows[0]['user_id'];

        if (row.child.isShown()) {
            tr.removeClass('details');
            row.child.hide();
            //Ndryshojme ikonen kur tabela mbyllet
            tr.find('.fas').attr('class', 'fas fa-plus-circle fa-lg text-success');
            openedTables.delete(index + 1);
        } else {
            openedTables.add(index + 1);

            tr.addClass('details');

            row.child(format_tbl2_html()).show();
            initialize_table_2(user_id);

            tr.find('.fas').attr('class', 'fas fa-minus-circle fa-lg text-danger');
        }
    });

    function load_checkins(tbl1) {
        tbl1.on('draw', function() {
            var data = window.employee_data;
            var i = 1;

            for (var key in data) {
                if (Object.prototype.hasOwnProperty.call(data, key)) {
                    var val1 = data[key];

                    var total_hours_in = 0;
                    var normal_hours = 0;
                    var normal_salary = 0;
                    var overtime = 0;
                    var overtime_salary = 0;
                    var salary = 0;
                    var total_count = 0;

                    var id = key;
                    //shkojme edhe nje dimension tjeter me posht(kemi informacione te pergjithme
                    //per secilen date
                    for (key in val1) {
                        if (Object.prototype.hasOwnProperty.call(val1, key)) {
                            var val2 = val1[key];

                            if (val2['user_id'] === id) {
                                for (key in val2) {
                                    if (Object.prototype.hasOwnProperty.call(val2, key)) {
                                        var val3 = val2[key];

                                        if (isObject(val3)) {
                                            var object = Object.values(val3).map(({
                                                                                      normal_hours, overtime, k1, k2
                                                                                  }) => ({
                                                normal_hours, overtime, k1, k2
                                            }));

                                            //repeat for each date if a week has multiple days
                                            for (j = 0; j < object.length; j++) {
                                                normal_hours += object[j].normal_hours;
                                                normal_salary += object[j].k1 * object[j].normal_hours;

                                                overtime += object[j].overtime;
                                                overtime_salary += object[j].k2 * object[j].overtime;

                                                total_count += 1;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    normal_salary *= 10;
                    overtime_salary *= 10;
                    total_hours_in = normal_hours + overtime;
                    salary = (normal_salary + overtime_salary) / 3600;

                    //Marrim te dhenat e oreve totale dhe i shfaqim per cdo rrjesht
                    $(`#normalh-${i}`).text(sec_to_hour(normal_hours, 1));
                    $(`#normals-${i}`).text('$ ' + (normal_salary / 3600).toFixed(2));
                    $(`#overtimeh-${i}`).text(sec_to_hour(overtime, 1));
                    $(`#overtimes-${i}`).text('$ ' + (overtime_salary / 3600).toFixed(2));
                    $(`#totalh-${i}`).text(sec_to_hour(total_hours_in, 1));
                    $(`#sph-${i}`).text('$ ' + ((salary / total_hours_in) * 3600).toFixed(2));
                    $(`#salary-${i}`).text('$ ' + Math.round(salary));
                    var days;
                    if (total_count === 1) days = ' day'; else days = ' days';

                    $(`#dates-${i}`).text(total_count + days);

                    i++;
                }
            }
        });
    }

    function format_tbl2_html() {
        /**
         * Bejme draw tabelen e meposhte
         */
        return `
                <table class='innerTable tableclass2 display' style='width:100%'>
                   <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th class='text-right'>Week</th>
                            <th class='text-right'>Normal Hours</th>
                            <th class='text-right'>Normal Salary</th>
                            <th class='text-right'>Overtime Hours</th>
                            <th class='text-right'>Overtime Salary</th>
                            <th class='text-right'>Total Hours</th>
                            <th class='text-right'>Salary/hour</th>
                            <th class='text-right'>Total Salary</th>
                        </tr>
                    </thead>
                </table>
`;
    }

    /**
     * Inicializojme tabelen e dyte
     * @param id
     */
    function initialize_table_2(id) {
        //Ruajme te dhena me id-ne e userit si key
        employee = window.employee_data[id];

        //E ekzekutojme vetem nese kemi te pakten nje ID
        //ne menyre qe te bejme prevent errorin
        if (employee !== undefined) {
            //Mapojme nga backendi vetem te dhenat e pergjithshme per secilen date
            var table_data = Object.values(employee).map(({
                                                              week,
                                                              user_id,
                                                              dates,
                                                              normal_hours,
                                                              normal_salary,
                                                              overtime,
                                                              overtime_salary,
                                                              total_hours,
                                                              total_salary
                                                          }) => ({
                week, user_id, dates, normal_hours, normal_salary, overtime, overtime_salary, total_hours, total_salary
            }));

            const holidays = ['01-01', '03-14', '03-22', '04-17', '04-18', '05-01', '05-02', '05-13', '07-20', '09-05', '11-28', '11-29', '12-08', '05-25'];

            //konvertojme te dhenat e mesiperme nga seconda ne kohe
            i = 0;
            table_data.forEach(function(result) {
                var a = table_data[i].dates;

                for (var key in a) {
                    if (Object.prototype.hasOwnProperty.call(a, key)) {
                        var val1 = a[key];

                        var normal_hours = val1['normal_hours'] / 3600;
                        var over_time = val1['overtime'] / 3600;
                        var total_hours = normal_hours + over_time;

                        result.normal_hours += normal_hours;
                        result.overtime += over_time;
                        result.total_hours += total_hours;

                        var date = val1['check_in_date'];
                        if (holidays.indexOf(date.slice(5)) > -1) {
                            var k1 = 1.5;
                            var k2 = 2;
                        } else if (is_weekend(date)) {
                            var k1 = 1.25;
                            var k2 = 1.5;
                        } else {
                            var k1 = 1;
                            var k2 = 1.25;
                        }

                        result.normal_salary += normal_hours * k1 * 10;
                        result.overtime_salary += over_time * k2 * 10;
                        result.total_salary += (normal_hours * k1 + over_time * k2) * 10;
                    }
                }
                result.salary_per_hour = '$ ' + (result.total_salary / result.total_hours).toFixed(2);
                result.normal_salary = '$ ' + result.normal_salary.toFixed(2);
                if (result.overtime_salary == 0) {
                    result.overtime_salary = '-';
                } else {
                    result.overtime_salary = '$ ' + result.overtime_salary.toFixed(2);
                }
                result.total_salary = '$ ' + result.total_salary.toFixed(2);

                result.overtime = sec_to_hour(3600 * result.overtime);
                result.normal_hours = sec_to_hour(3600 * result.normal_hours);
                result.total_hours = sec_to_hour(3600 * result.total_hours);

                i++;
            });
        } else {
            //send empty data if no data
            table_data = [];
        }

        //inicializojme tabelen dytesore
        window.tbl2 = $(`.tableclass2`).DataTable({
            pageLength: 5,
            lengthMenu: [5, 20, 50, 75, 100],
            retrieve: true,
            paging: true,
            searching: true,
            info: true,
            data: table_data,
            columns: [{
                className: 'details-control2 ',
                orderable: false,
                data: '',
                width: '5%',
                defaultContent: '<i class="fas fa-plus-circle fa-lg text-dark" style="font-size:25px" aria-hidden="true"></i>'
            }, {
                className: 'dt-body-right', data: 'user_id'
            }, {
                className: 'dt-body-right', data: 'week'
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
                className: 'dt-body-right', data: 'salary_per_hour'
            }, {
                className: 'dt-body-right', data: 'total_salary'
            }],
            columnDefs: [{
                targets: [1], visible: false, searchable: true
            }]
        });

        second_table_details_control(tbl2, table_data);
    }

    function second_table_details_control(tbl2) {
        $('.tableclass2 tbody').on('click', 'td.details-control2', function(event) {
            var tr = $(this).closest('tr');

            var row = tbl2.row(tr);
            var index = parseInt(row[0]);

            var rows = tbl2.rows(index).data();
            var user_id = rows[0]['user_id'];

            var week = rows[0]['week'];

            if (row.child.isShown()) {
                // Nese rresht eshte i hapur, e mbyllim
                row.child.hide();

                //Ndryshim ikone ne hide
                tr.find('.fa-minus-circle').attr('class', 'fas fa-plus-circle fa-lg text-dark');
            } else {
                // initialize_table_3(user_id);
                row.child(format_tbl3_html()).show();
                initialize_table_3(user_id, week);

                //Ndryshim ikone ne show
                tr.find('.fa-plus-circle').attr('class', 'fas fa-minus-circle fa-lg text-dark');
            }
        });
    }

    function format_tbl3_html() {
        //Shtojme formatimin e tabeles
        return `<table class='innerTable display tableclass3' style='width:100%'>
<thead>
    <tr>
        <th>Date</th>
        <th>Normal Hours</th>
        <th>N-Salary</th>
        <th>Overtime</th>
        <th>O-Salary</th>
        <th>Total Hours</th>
        <th>Pay per hour</th>
        <th>Pay per date</th>
    </tr>
</thead>
</table>`;
    }

    function initialize_table_3(id, week) {
        //ruajme objektin qe permban te gjitha checkins e asaj dite specifike
        var tb2_val = window.employee_data[id][week]['dates'];

        //tani qe kemi ID dhe DATEN specifike, marrim te gjitha checkins
        //e asaj dite dhe i ruajme ne variabel
        var checkins = Object.values(tb2_val).map(({ check_in_date, normal_hours, overtime, hours_per_date }) => ({
            check_in_date, normal_hours, overtime, hours_per_date
        }));
        console.log(checkins);

        const holidays = ['01-01', '03-14', '03-22', '04-17', '04-18', '05-01', '05-02', '05-13', '07-20', '09-05', '11-28', '11-29', '12-08', '05-25'];

        checkins.forEach(function(result) {
            result.hours_per_date = result.normal_hours + result.overtime;

            var date = result['check_in_date'];
            if (holidays.indexOf(date.slice(5)) > -1) {
                var k1 = 1.5;
                var k2 = 2;
            } else if (is_weekend(date)) {
                var k1 = 1.25;
                var k2 = 1.5;
            } else {
                var k1 = 1;
                var k2 = 1.25;
            }

            result.normal_salary = (result.normal_hours * k1 * 10) / 3600;
            result.overtime_salary = (result.overtime * k2 * 10) / 3600;
            result.total_salary = result.normal_salary + result.overtime_salary;
            result.salary_hour = '$ ' + ((result.total_salary / result.hours_per_date) * 3600).toFixed(2);

            result.normal_salary = '$ ' + result.normal_salary.toFixed(2);
            if (result.overtime_salary == 0) result.overtime_salary = '-'; else result.overtime_salary = '$ ' + result.overtime_salary.toFixed(2);
            result.total_salary = '$ ' + result.total_salary.toFixed(2);

            result.normal_hours = sec_to_hour(result.normal_hours, 0);
            result.overtime = sec_to_hour(result.overtime, 0);
            result.hours_per_date = sec_to_hour(result.hours_per_date, 0);

            console.log(result);
        });

        console.log(checkins);

        // checkins.sort((a, b) => a.check_in_hour.localeCompare(b.check_in_hour));

        //Inicializojme tabelen e trete.
        $(`.tableclass3`).DataTable({
            pageLength: 5,
            lengthMenu: [5, 20, 50, 75, 100],
            retrieve: true,
            paging: false,
            searching: false,
            info: false,
            data: checkins,

            columns: [{
                data: 'check_in_date'
            }, {
                data: 'normal_hours'
            }, {
                data: 'normal_salary'
            }, {
                data: 'overtime'
            }, {
                data: 'overtime_salary'
            }, {
                data: 'hours_per_date'
            }, {
                data: 'salary_hour'
            }, {
                data: 'total_salary'
            }]
        });
    }

    function sec_to_hour(value, i) {
        const sec = parseInt(value, 10); // convert value to number if it's string
        let hours = Math.floor(sec / 3600); // get hours
        let minutes = Math.floor((sec - hours * 3600) / 60); // get minutes
        // add 0 if value < 10; Example: 2 => 02
        if (hours === 0) return '-';

        if (hours < 10) {
            hours = ' ' + hours;
        }
        if (minutes < 10) {
            minutes = '0' + minutes;
        }

        if (i === 0) return hours + ' h ' + minutes + ' min';
        // Return is HH : MM : SS
        else {
            return hours + ' hours';
        }
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
