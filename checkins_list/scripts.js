$(function () {

    console.log("worked");

    //initilaize table only once instead of multiple times
    //here we save the record

    //here we save the opened tbl2 rows in array
    //so when user clicks
    var openedTables = new Set();


    load_daterangepicker();
    load_add_checkins();


    /**
     * Bejme inicializimin e tabeles kryesore
     */
    var tbl1 = $('#checkins_list').DataTable({
        "processing": true, "serverSide": true, "ordering": true, pageLength: 10, lengthMenu: [5, 10, 20], "ajax": {
            "data": function (data) {
                data.action = 'load_table'
                //Ne momentin qe bejme load tabelen, backendi merr diten e sotme minus 30 dite

                var date_s = $('#date');
                var startDate = date_s.data('daterangepicker').startDate.format("YYYY-MM-DD");
                var endDate = date_s.data('daterangepicker').endDate.format("YYYY-MM-DD");
                data.startDate = startDate;
                data.endDate = endDate;

            }, 'url': 'ajax.php', 'type': 'post'
        }, "columns": [{
            "data": "user_id",
        }, {
            "class": "details-control1",
            "orderable": false,
            "data": null,
            "defaultContent": '<i class="fas fa-plus-circle fa-lg text-success" style="font-size:25px" aria-hidden="true"></i>'
        }, {
            "data": "first_name"
        }, {
            "data": "dates"
        }, {
            "data": "normal_hours"
        }, {
            "data": "overtime"
        }, {
            "data": "total_hours_in"
        }, {
            "data": "salary_per_hour"
        }, {
            "data": "salary"
        }], "columnDefs": [{
            "targets": [0], "visible": false, "searchable": true, "width": "0%"
        }], "createdRow": function (row, data, dataIndex) {
            /**
             * EDIT ROW DATA
             */
            if (data['show']) {
                $(row).children('td').eq(0).html(`<button id="button-'${dataIndex + 1}" class="show" style="border:none;background:none;margin-top:8px" class="button-primary" value="${dataIndex + 1}"><i class="fas fa-plus-circle text-success" style="font-size:25px" ></i></button>`)
            }

            if (data['dates']) {
                $(row).children('td').eq(2).html(`<span id="dates-${dataIndex + 1}"></span>`)
            }

            if (data['normal_hours']) {
                $(row).children('td').eq(3).html(`<span id="normal-${dataIndex + 1}"></span>`)
            }

            if (data['overtime']) {
                $(row).children('td').eq(4).html(`<span id="overtime-${dataIndex + 1}"></span>`)
            }

            if (data['total_hours_in']) {
                $(row).children('td').eq(5).html(`<span id="hours-${dataIndex + 1}"></span>`)
            }

            if (data['salary_per_hour']) {
                $(row).children('td').eq(6).html(`<span id="salary_per_hour-${dataIndex + 1}"></span>`)
            }

            if (data['salary']) {
                $(row).children('td').eq(7).html(`<span id="salary-${dataIndex + 1}"></span>`)
            }

        }, "drawCallback": function (settings) {
            window.checkins = settings.json['checkinsData']
            load_inner_tables(checkins)

            setTimeout(function () {
                for (let item of openedTables.values()) $('.details-control1').eq(item).trigger('click')

            }, 200);


        }, "initComplete": function (settings, json) {

            /**
             * Search delay
             */
            var search_s = $("div.dataTables_filter input");
            search_s.unbind();

            search_s.on("keyup", delay(function (event) {
                openedTables.clear();
                tbl1.search(this.value).draw()

            }, 700));
            /**
             * End of search delay
             */
        }
    })

    function load_inner_tables(json) {


        id = Object.keys(json);
        var i = 0;
        tbl1.rows().eq(0).each(function (index) {

            var row = tbl1.row(index);
            row.child(format_tbl2_html()).show()
            initialize_table_2(id[i])

            //Ndalojme inicializimin e tabeles se trete nese tabela e dyte eshte b0sh
            if (json[id[i]] === undefined) {
                row.child.hide()
                return
            }

            var dates = Object.keys(json[id[i]])
            var j = 0
            tbl2.rows().eq(0).each(function (index) {

                var row = tbl2.row(index);
                row.child(format_tbl3_html(row.data())).show();
                initialize_table_3(id[i], dates[j]);
                row.child.hide()
                j++;
            });

            row.child.hide()
            i++
            // ... do something with data(), or row.node(), etc
        });
    }


    /**
     * Clears opened tables array which auto showed all previously opened tables
     */

    tbl1.on('search', function (event) {

        console.log('el')
        // load_inner_tables(checkins)
        // openedTables.clear();
        // console.log('execyted')
        return false;
    })

    $('#applyFilter').on('click', function () {
        tbl1.draw();
    })

    $('#checkins_list tbody').on('click', 'tr td.details-control1', function (event) {
        var tr = $(this).closest('tr');
        var row = tbl1.row(tr);
        var index = parseInt(row[0]) + 1;

        if (row.child.isShown()) {
            tr.removeClass('details');
            row.child.hide();
            //Ndryshojme ikonen kur tabela mbyllet
            tr.find('.fas').attr('class', 'fas fa-plus-circle fa-lg text-success')
            openedTables.delete(index + 1);

        } else {
            openedTables.add(index + 1);

            tr.addClass('details');

            row.child.show();

            tr.find('.fas').attr('class', 'fas fa-minus-circle fa-lg text-danger')

        }

    });


    load_checkins(tbl1)


    function load_checkins(tbl1) {
        tbl1.on('draw', function () {

            var data = window.checkins
            var total_hours_in = 0;
            var normal_hours = 0;
            var total_count = 0;
            var i = 1;

            for (var key in data) {
                if (Object.prototype.hasOwnProperty.call(data, key)) {

                    var val1 = data[key];
                    var overtime = 0;
                    var salary = 0;
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
                                        // console.log(val2['user_id']);

                                        if (isObject(val3)) {

                                            var object = Object.values(val3).map(({
                                                                                      pay_per_checkin
                                                                                  }) => ({
                                                pay_per_checkin
                                            }))

                                            //returns array with only the values of the date
                                            var array = object.map(x => x.pay_per_checkin)
                                            // sums all values inside the above array and returns an int
                                            salary += array.reduce((pv, cv) => pv + cv, 0);

                                        }
                                    }
                                }
                                total_hours_in += val2['hours_per_date'];
                                overtime += val2['overtime'];
                                total_count += 1
                            }

                        }
                    }

                    normal_hours = total_hours_in - overtime;
                    //Marrim te dhenat e oreve totale dhe i shfaqim per cdo rrjesht
                    $(`#hours-${i}`).text(sec_to_hour(total_hours_in, 1));
                    $(`#normal-${i}`).text(sec_to_hour(normal_hours, 1));
                    $(`#overtime-${i}`).text(sec_to_hour(overtime, 1));
                    $(`#salary-${i}`).text("$ " + Math.round((salary)));
                    $(`#salary_per_hour-${i}`).text("$ " + ((salary / total_hours_in * 3600)).toFixed(2));

                    var days;
                    if (total_count === 1) days = " day"; else days = " days"

                    $(`#dates-${i}`).text(total_count + days);

                    //i bejme reset qe te llogaritet sakte per cdo user
                    total_hours_in = 0
                    overtime = 0;
                    total_count = 0;
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
                <table class="innerTable tableclass2 display" style="width:100%">
                   <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th class="text-right">Date</th>
                            <th class="text-right">Check in count</th>
                            <th class="text-right">Normal hours</th>
                            <th class="text-right">Overtime</th>
                            <th class="text-right">Total/Date</th>
                            <th class="text-right">Salary/Hour</th>
                            <th class="text-right">Salary</th>
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
        var user_checkins = window.checkins[id];

        //E ekzekutojme vetem nese kemi te pakten nje ID
        //ne menyre qe te bejme prevent errorin
        if (user_checkins !== undefined) {
            //Mapojme nga backendi vetem te dhenat e pergjithshme per secilen date
            var table_data = Object.values(user_checkins).map(({
                                                                   user_id,
                                                                   check_in_date,
                                                                   hours_per_date,
                                                                   normal_hours,
                                                                   overtime,
                                                                   count,
                                                                   pay_per_day,
                                                                   pay_per_hour,

                                                               }) => (

                {
                    user_id, check_in_date, hours_per_date, normal_hours, overtime, count, pay_per_day, pay_per_hour
                }))
            var y = new Date().getFullYear();
            const holidays = [y + "-01-01", y + "-03-14", y + "-03-22", y + "-04-17", y + "-04-18", y + "-05-01", y + "-05-02", y + "-05-13", y + "-07-20", y + "-09-05", y + "-11-28", y + "-11-29", y + "-12-08", y + "-05-25"];


            //konvertojme te dhenat e mesiperme nga seconda ne kohe
            table_data.forEach(function (result) {
                var normal_hours = result.normal_hours;
                var overtime = result.overtime;

                if (holidays.indexOf(result.check_in_date) > -1) {
                    var k1 = 1.5;
                    var k2 = 2;
                } else if (is_weekend(result.check_in_date)) {
                    var k1 = 1.25;
                    var k2 = 1.5;
                } else {
                    var k1 = 1;
                    var k2 = 1.25;
                }

                result.normal_hours = sec_to_hour(result.hours_per_date - (result.overtime), 0)
                result.hours_per_date = sec_to_hour(result.hours_per_date, 0)
                result.overtime = sec_to_hour(result.overtime, 0)
                result.pay_per_day = ((normal_hours * 10 * k1 + overtime * 10 * k2) / 3600).toFixed(2);
                result.pay_per_hour = (result.pay_per_day / (normal_hours + overtime) * 3600).toFixed(2)

                result.pay_per_day = "$ " + result.pay_per_day;
                result.pay_per_hour = "$ " + result.pay_per_hour

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
                "className": 'details-control2 ',
                "orderable": false,
                "data": null,
                "width": "12%",
                "defaultContent": '<i class="fas fa-plus-circle fa-lg text-dark" style="font-size:25px" aria-hidden="true"></i>'
            }, {
                "className": 'dt-body-right', data: "user_id"
            }, {
                "className": 'dt-body-right', data: "check_in_date"
            }, {
                "className": 'dt-body-right', data: "count"
            }, {
                "className": 'dt-body-right', data: "normal_hours"
            }, {
                "className": 'dt-body-right', data: "overtime"
            }, {
                "className": 'dt-body-right', data: "hours_per_date"
            }, {
                "className": 'dt-body-right', data: "pay_per_hour"
            }, {
                "className": 'dt-body-right', data: "pay_per_day"
            },],
            "columnDefs": [{
                "targets": [1], "visible": false, "searchable": true, "width": "0%"
            }]

        });

        second_table_details_control(tbl2)
    }

    function second_table_details_control(tbl2) {


        $('.tableclass2 tbody').on('click', 'td.details-control2', function (event) {

            var tr = $(this).closest('tr');

            var row = tbl2.row(tr);

            if (row.child.isShown()) {
                // Nese rresht eshte i hapur, e mbyllim
                row.child.hide();

                //Ndryshim ikone ne hide
                tr.find('.fa-minus-circle').attr('class', 'fas fa-plus-circle fa-lg text-dark');

            } else {
                // initialize_table_3(user_id);
                row.child.show()
                //Ndryshim ikone ne show
                tr.find('.fa-plus-circle').attr('class', 'fas fa-minus-circle fa-lg text-dark')
            }

        });

    }

    function format_tbl3_html() {

        //Shtojme formatimin e tabeles
        return `<table class="innerTable display tableclass3" style="width:100%">
<thead>
    <tr>
        <th>Date in</th>
        <th>Check in</th>
        <th>Check out</th>
        <th>Date out</th>
        <th>Pay per Checkin</th>

        
    </tr>
</thead>
</table>`
    }

    function initialize_table_3(id, date) {


        //ruajme objektin qe permban te gjitha checkins e asaj dite specifike
        var tb2_val = window.checkins[id][date]['checkins_per_day'];

        //tani qe kemi ID dhe DATEN specifike, marrim te gjitha checkins
        //e asaj dite dhe i ruajme ne variabel
        var checkins = Object.values(tb2_val).map(({
                                                       check_in_date,
                                                       check_in_hour,
                                                       check_out_hour,
                                                       check_out_date,
                                                       pay_per_checkin
                                                   }) => ({
            check_in_date, check_in_hour, check_out_hour, check_out_date, pay_per_checkin
        }))

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
            "createdRow": function (row, data, dataIndex) {
                /**
                 * EDIT ROW DATA
                 */
                if (data['pay_per_checkin']) {
                    $(row).children('td').eq(4).html(`$ ${data['pay_per_checkin']}`)
                }
            },
            columns: [{
                data: "check_in_date"
            }, {
                data: "check_in_hour"
            }, {
                data: "check_out_hour"
            }, {
                data: "check_out_date"
            }, {
                data: "pay_per_checkin"
            }]
        });
    }

    function sec_to_hour(value, i) {
        const sec = parseInt(value, 10); // convert value to number if it's string
        let hours = Math.floor(sec / 3600); // get hours
        let minutes = Math.floor((sec - (hours * 3600)) / 60); // get minutes
        // add 0 if value < 10; Example: 2 => 02
        if (hours < 10) {
            hours = " " + hours;
        }
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        if (hours === 0) return "-";

        if (i === 0) return hours + ' h ' + minutes + ' min'; // Return is HH : MM : SS

        else {
            return hours + " hours"
        }

    }

    function load_daterangepicker() {
        $('#date,#daterange').daterangepicker({
            singleDatePicker: false,
            showDropdowns: true,
            startDate: moment().add(-30, 'day'),
            minYear: 1901,
            maxYear: parseInt(moment().format('YYYY'), 10),
            locale: {
                format: 'DD/MM/YYYY', cancelLabel: 'Clear'
            }
        })
    }

    function load_add_checkins() {
        $('#addCheckin').on('click', function (event) {
            event.preventDefault()

            /**
             * Marrim te dhenat nga useri
             */
            var email = $('#email').val()
            var checkin = $('#checkin').val()
            var checkout = $('#checkout').val()
            var date_s = $('#daterange');
            var checkin_date = date_s.data('daterangepicker').startDate.format("YYYY-MM-DD")
            var checkout_date = date_s.data('daterangepicker').endDate.format("YYYY-MM-DD")

            //I cojme ne backend
            $.ajax({
                url: "ajax.php", method: "POST", data: {
                    action: 'add_checking',
                    email: email,
                    checkin: checkin,
                    checkout: checkout,
                    checkin_date: checkin_date,
                    checkout_date: checkout_date
                }, cache: false, beforeSend: function (xhr) {
                    $("button").attr("disabled", "disabled");
                }, success: function (response) {

                    response = JSON.parse(response)
                    if (response.status != 200) {
                        Swal.fire('Error!', response['message'], 'error',)
                    } else {
                        Swal.fire('Success!', response['message'], 'success',)
                    }
                    setTimeout(function () {
                        $('button').prop("disabled", false);
                    }, 1500);
                }
            });
        });
    }

});