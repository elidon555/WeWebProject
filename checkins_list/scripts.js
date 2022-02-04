$(function () {

    console.log("worked");

    //initilaize table only once instead of multiple times
    //here we save the record
    var formatTableOnce = [];

    //here we save the opened tbl2 rows in array
    //so when user clicks
    var openedTables = new Set();

    load_main_table();
    load_daterangepicker();
    load_add_checkins();

    function load_main_table() {

        /**
         * Bejme inicializimin e tabeles kryesore
         */
        var tbl1 = $('#checkins_list').DataTable({
            "processing": true, "serverSide": true, "ordering": true, pageLength: 10, lengthMenu: [5, 10, 20], "ajax": {
                "data": function (data) {
                    data.action='load_table'
                    //Ne momentin qe bejme load tabelen, backendi merr diten e sotme minus 30 dite
                    if (window.allowDateFilter === 1) {
                        /**
                         * Mbasi behet draw tabela njehere, tani mund te aplikojme filtrin e dates
                         */
                        var date_s = $('#date');
                        var startDate = date_s.data('daterangepicker').startDate.format("YYYY-MM-DD");
                        var endDate = date_s.data('daterangepicker').endDate.format("YYYY-MM-DD");
                        data.startDate = startDate;
                        data.endDate = endDate;
                    }
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
                "data": "total_hours_in"
            }, {
                "data": "total_hours_out"
            }, {
                "data": "dates"
            }], "columnDefs": [{
                "targets": [0], "visible": false, "searchable": true, "width": "0%"
            }], "drawCallback": function (settings) {

                /**
                 * Bejme qe te hapen child rows se tabeles kryesore
                 * vetem ne momentin qe useri ben filter daten
                 */
                window.allowDateFilter = 1;
                window.checkins = settings.json['checkinsData'];

                setTimeout(function () {
                    for (let item of openedTables.values()) $('.details-control1').eq(item).trigger('click')

                }, 200);

                //bejme empty array ne menyre qe kur te bejme data reload
                // te behet riinicializimi i tabeles perseri
                formatTableOnce = [];
            }, "initComplete": function (settings, json) {
                /**
                 * Search on click
                 */
                var input = $('.dataTables_filter input').unbind(), self = this.api(), $searchButton = $('<button>')
                    .html('<i class="fas fa-search text-success"></i>')
                    .click(function () {
                        self.search(input.val()).draw();
                    }), $clearButton = $('<button>')
                    .html('<i class="fas fa-search-minus text-danger"></i>')
                    .click(function () {
                        input.val('');
                        $searchButton.click();
                    })
                $('.dataTables_filter').append($searchButton, $clearButton);
                /**
                 * End of search on click
                 */
            }
        })


        /**
         * Clears opened tables array which auto showed all previously opened tables
         */
        $('input[type="search"]').on('keydown', function () {
            openedTables.clear();
        })

        tbl1.on('page.dt', function () {
            openedTables.clear();
        })


        main_tbl_details_control(tbl1)
        load_checkins(tbl1)


    }

    function load_checkins(tbl1) {
        tbl1.on('draw', function () {

            var data = window.checkins
            var total_hours_in = 0;
            var total_hours_out = 0;
            var total_count = 0;
            var i = 1;

            for (var key in data) {
                if (Object.prototype.hasOwnProperty.call(data, key)) {

                    var val1 = data[key];

                    var id = key;
                    //shkojme edhe nje dimension tjeter me posht(kemi informacione te pergjithme
                    //per secilen date
                    for (key in val1) {

                        if (Object.prototype.hasOwnProperty.call(val1, key)) {
                            var val2 = val1[key];
                            // console.log(val2);
                            if (val2['user_id'] === id) {
                                total_hours_in += val2['total_hours_in'];
                                total_hours_out += val2['total_hours_out'];
                                total_count += 1
                            }
                        }
                    }
                    //Marrim te dhenat e oreve totale dhe i shfaqim per cdo rrjesht
                    $(`#hours-${i}`).text(sec_to_hour(total_hours_in, 1) + " worked");
                    $(`#hours-out-${i}`).text(sec_to_hour(total_hours_out, 1) + " off");

                    var days;
                    if (total_count === 1) days = " day"; else days = " days"

                    $(`#dates-${i}`).text(total_count + days);

                    //i bejme reset qe te llogaritet sakte per cdo user
                    total_hours_in = 0
                    total_hours_out = 0;
                    total_count = 0;
                    i++;
                }
            }
        });
    }

    function main_tbl_details_control(tbl1) {
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
                //Ndalon ri-inicializimin e tabeles kur klikojme
                // show details tek tabela dytesore
                if (isNaN(index)) return;

                var data = tbl1.row($(this).parents('tr')).data();
                var row_user_id = data['user_id'];
                tr.addClass('details');

                /**
                 * Inicializimi i tabeles, optimizim
                 * I ruajme ne array listen e tabelave te inicializuara
                 */
                if (!formatTableOnce.includes(row_user_id)) {

                    //E bejme vetem nese nuk eshte inicializuar me pare
                    row.child(format_tbl2_html(index)).show();
                    initialize_table_2(row_user_id)
                    formatTableOnce.push(row_user_id);

                } else {
                    //Nese eshte inicializuar, thjesht e bejme show
                    row.child.show();
                }
                // Add to the 'open' array

                //Ndryshojme ikonen kur tabela hapet

                tr.find('.fas').attr('class', 'fas fa-minus-circle fa-lg text-danger')

                //Put table index on this array so when we search
                //another date,  we keep it opened
            }
            event.stopImmediatePropagation()
        });
    }

    function format_tbl2_html(row) {
        /**
         * Bejme draw tabelen e meposhte
         */
        return `
                <table id="table${row}" class="innerTable tableclass2 display" style="width:100%">
                   <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th class="text-right">Dates in</th>
                            <th class="text-right">Check in total</th>
                            <th class="text-right">Check out total</th>
                            <th class="text-right">Check in count</th>
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
                                                                   total_hours_in,
                                                                   total_hours_out,
                                                                   count
                                                               }) => (

                {
                    user_id, check_in_date, total_hours_in, total_hours_out, count
                }))
            //konvertojme te dhenat e mesiperme nga seconda ne kohe
            table_data.forEach(function (result) {
                result.total_hours_in = sec_to_hour(result.total_hours_in, 0)
                result.total_hours_out = sec_to_hour(result.total_hours_out, 0)
            });
        } else {
            //send empty data if no data
            table_data = [];
        }


        //inicializojme tabelen dytesore
        var tbl2 = $(`.tableclass2`).DataTable({
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
                "className": 'dt-body-right', data: "total_hours_in"
            }, {
                "className": 'dt-body-right', data: "total_hours_out"
            }, {
                "className": 'dt-body-right', data: "count"
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

            console.log(row[0]);

            if (row.child.isShown()) {
                // Nese rresht eshte i hapur, e mbyllim
                row.child.hide();

                //Ndryshim ikone ne hide
                tr.find('.fa-minus-circle').attr('class','fas fa-plus-circle fa-lg text-dark');

            } else {
                // Hapim rreshtin

                //Ruajme te gjitha te dhenat e rrjeshtit ne kete variabel
                var data = tbl2.row($(this).parents('tr')).data();

                //ruajme id-ne e userit ne variable
                var user_id = data['user_id'];

                //formatojme
                row.child(format_tbl3_html(row.data())).show();
                initialize_table_3(parseInt(user_id));

                //Ndryshim ikone ne show
                tr.find('.fa-plus-circle').attr('class','fas fa-minus-circle fa-lg text-dark')
            }
            //Stop the button from clicking twice somehow
            //because before this, it used to cause a doubleclick bug
            //when switching from one user to another user id.
            event.stopImmediatePropagation()
        });
    }

    function format_tbl3_html(d) {

        //Ruajme ne variabel globale daten
        window.date = d.check_in_date

        //Shtojme formatimin e tabeles
        return `<table class="innerTable display tableclass3" style="width:100%">
<thead>
    <tr>
        <th>Date in</th>
        <th>Check in</th>
        <th>Check out</th>
        <th>Date out</th>
    </tr>
</thead>
</table>`
    }

    function initialize_table_3(id) {


        var date = window.date;

        //ruajme objektin qe permban te gjitha checkins e asaj dite specifike
        var tb2_val = window.checkins[id][date]['checkins_per_day'];

        //tani qe kemi ID dhe DATEN specifike, marrim te gjitha checkins
        //e asaj dite dhe i ruajme ne variabel
        var checkins = Object.values(tb2_val).map(({
                                                       check_in_date, check_in_hour, check_out_hour, check_out_date
                                                   }) => ({
            check_in_date, check_in_hour, check_out_hour, check_out_date
        }))

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
                data: "check_in_date"
            }, {
                data: "check_in_hour"
            }, {
                data: "check_out_hour"
            }, {
                data: "check_out_date"
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
        if (i === 0) return hours + ' h ' + minutes + ' min'; // Return is HH : MM : SS
        else return hours + " hours"
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