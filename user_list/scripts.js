function create_new_user() {

    /**
     * Marrim te henat qe ka plotesuar perdoruesi
     */
    var first_name = $('#First_name').val();
    var last_name = $('#Last_name').val();
    var atesia = $('#Atesia').val();
    var date = $('#Date').val();
    var email = $('#Email').val();
    var phone_number = $('#Phone_number').val();
    var password = $('#Password').val();
    var confirm_password = $('#Confirm_password').val();
    var files = $('#File')[0].files[0];
    var fd = new FormData();


    /**
     * Validimi i te dhenave
     */

        //Bejme validate te gjitha fushat
    var error = validate_submit();

    //Bejme validate foton nese useri e shton
    error += image_check(files);


    //Nese nuk kemi errore, procedojme
    if (error == 0) {

        var role;

        //I shtojme rolin userit
        if ($('#roleUser').is(':checked')) {
            role = "Admin";
        } else {
            role = "User";
        }
        /////////

        /**
         * Ruajme te dhenat ne form data
         */
        fd.append('file', files);
        fd.append('add', "1");
        fd.append('id', window.id);
        fd.append('first_name', first_name);
        fd.append('last_name', last_name);
        fd.append('atesia', atesia);
        fd.append('date', date)
        fd.append('email', email);
        fd.append('phone_number', phone_number);
        fd.append('password', password);
        fd.append('confirm_password', confirm_password);
        fd.append('role', role);

        /**
         * I dergojme backend-it te gjitha te dhenat
         */
        $.ajax({
            url: "ajax.php", method: "POST", data: fd, contentType: false, processData: false, cache: false,

            success: function (response) {

                response = JSON.parse(response);

                if (response.status != 200) {
                    Swal.fire('Error!', response['message'], 'error',)
                } else {
                    Swal.fire('Success!', 'Your account registered successfully', 'success',)
                    $('.modal-footer >button:first-child').click()
                    $('#loadAfterAction').click()
                }
            }, dataType: 'text'
        });
        /**
         * EDIT USER END
         */
        return true;

    } else {

        Swal.fire('Please enter all the fields correctly!', '', 'error',)

        return false;
    }
}
function edit_user() {

        var error = 0;

        /**
         * Marrim te henat qe ka plotesuar perdoruesi
         */

        var first_name = $('#first_name').val();
        var last_name = $('#last_name').val();
        var atesia = $('#atesia').val();
        var date = $('#date').val();
        var email = $('#email').val();
        var phone_number = $('#phone_number').val();
        var password = $('#password').val();
        var confirm_password = $('#confirm_password').val();
        var files = $('#file')[0].files[0];
        var fd = new FormData();


        /**
         * Validimi i te dhenave
         */

        // validimi i emrit

        error += validate_submit()
        error += image_check()



        //Nese nuk kemi errore,procedojme
        if (error === 0) {

            var role;

            if ($('#roleUser').is(':checked')) {
                role = "User";
            } else {
                role = "Admin";
            }
            /////////

            fd.append('file', files);
            fd.append('update', "1");
            fd.append('id', window.id);
            fd.append('first_name', first_name);
            fd.append('last_name', last_name);
            fd.append('atesia', atesia);
            fd.append('date', date)
            fd.append('email', email);
            fd.append('phone_number', phone_number);
            fd.append('password', password);
            fd.append('confirm_password', confirm_password);
            fd.append('role', role);

            $.ajax({
                url: "ajax.php", method: "POST", data: fd, contentType: false, processData: false, cache: false,

                success: function (response) {

                    response = JSON.parse(response);

                    if (response.status != 200) {
                        Swal.fire('Error!', response['message'], 'error',)
                    } else {
                        Swal.fire('Success!', 'Your account registered successfully', 'success',)
                        $('.modal-footer >button:first-child').click()
                        $('#loadAfterAction').click()
                    }
                }, dataType: 'text'
            });
            /**
             * EDIT USER END
             */
            return true;

        } else {

            Swal.fire('Please enter all the fields correctly!', '', 'error',)

            return false;
        }

}


$(function () {

    /**
     * Bejme inicializimin e tabeles user_list
     */
    var table = $('#user_list').DataTable({
        "processing": true, "serverSide": true, "ordering": true, "orderCellsTop": true, "fixedHeader": true, "ajax": {
            'url': 'ajax.php', 'type': 'post', "data": function (d) {
                //Ne momentin qe bejme load tabelen, backendi merr diten e sotme minus 30 dite

                /**
                 * Mbasi behet draw tabela te pakten njehere, tani mund te aplikojme filtrin e dates
                 */
                if (window.allowFilter === 1) {
                    var email = $('#emailFilter').val();
                    var phone = $('#phoneFilter').val();
                    var date_s = $('#dateFilter');
                    if ($('#dateFilter').val() != "") {
                        var startDate = date_s.data('daterangepicker').startDate.format("YYYY-MM-DD");
                        var endDate = date_s.data('daterangepicker').endDate.format("YYYY-MM-DD");
                    }
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.email = email;
                    d.phone_number = phone;
                }

            },
        },

        "columns": [{
            "data": "user_id"
        }, {
            "data": "image_name"
        }, {
            "data": "first_name"
        }, {
            "data": "last_name"
        }, {
            "data": "atesia"
        }, {
            "data": "date_of_birth"
        }, {
            "data": "phone_number"
        }, {
            "data": "actions"
        },], "initComplete": function (settings, json) {
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
            window.allowFilter = 1

            let selector = $('#user_list_filter');
            selector.prepend('<label>Email:<input id="emailFilter" type="search" class="" placeholder="" aria-controls="user_list"></label>')
            selector.prepend('<label>Phone:<input id="phoneFilter" type="number" class="" placeholder="" aria-controls="user_list"></label>')
            selector.prepend('<label>Date:<input id="dateFilter" type="text" class="" placeholder="" aria-controls="user_list"> </label><i id="clearDate" onclick="" style="cursor:pointer" class="fas fa-eraser fa-2x"></i>')

            $("#user_list_filter").addClass('w-100 d-flex justify-content-between')

            var dateFilter_s = $('#dateFilter');

            dateFilter_s.daterangepicker({

                singleDatePicker: false,
                showDropdowns: true,
                minYear: 1901,
                autoUpdateInput: false,
                maxYear: parseInt(moment().format('YYYY'), 10),
                locale: {
                    cancelLabel: 'Clear'
                }
            })

            //Ne momentin qe aplikojme daterangepicker, inputi merr vleren e dates
            dateFilter_s.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });

            //Nese i japim cancel daterangepicker, fshijme vleren e inputit
            dateFilter_s.on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });

        }

    })


    /**
     * EDIT MODAL
     */
    $('#dateFilter').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    var edit_modal_s = $("#editModal")
    edit_modal_s.on('shown.bs.modal', function () {
        window.edit = 1;
        //Bejme load daterangepicker
        single_date_picker()
    });

    //Kur bejme hide edit modal, fshijme vleren e file input + bejme hide file preview
    edit_modal_s.on('hide.bs.modal', function () {
        document.getElementById('file').value = null;
        $('#blah2').hide()
    });
    /**
     * END OF EDIT MODAL
     */


    /**
     * SIGNUP MODAL
     */
    var signup_modal_s = $("#signupModal")

    //Kur bejme hide signup modal, fshijme vleren e file input + bejme hide file preview
    signup_modal_s.on('hide.bs.modal', function () {
        document.getElementById('File').value = null;
        $('#blah').hide()
    });

    signup_modal_s.on('shown.bs.modal', function () {
        window.edit = 0;
        //Bejme load daterangepicker
        single_date_picker()
    })
    /**
     * END OF SIGNUP MODAL
     */

    /**
     * Mbasi tabela behet draw,
     * Tani mund te ekzekutojme keto funksione
     */
    table.on('draw', function () {
        //Enable these functions when we draw the table
        load_single_user_detail()
        delete_user()
    });

    //full validation_check real time
    full_check()

    // submit_add_user()

    function delete_user() {
        //confirm delete yes or no
        $('.delete').on('click', function () {

            //Marrim vleren e butonit i cili ka ID-ne
            var id = $(this).attr("value");

            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    $.ajax({
                        url: "ajax.php", method: "POST", data: {
                            delete: 1, id: id

                        }, cache: false,

                        success: function (response) {

                            response = JSON.parse(response);

                            if (response.status != 200) {
                                Swal.fire('Error!', response['message'], 'error',)
                            } else {
                                Swal.fire('Success!', response['message'], 'success',)
                                $('#loadAfterAction').click()
                            }
                        }
                    })
                }
            });

        })
    }
    function load_single_user_detail() {
        $('nobr > button:first-child').on('click', function () {

           id = $(this).attr("value");

            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {id: window.id, load_single_user: 1},
                cache: false,
                success: function (data) {
                    data = JSON.parse(data);

                    /**
                     * Plotesojme formen me te gjitha te dhenat e marra nga backendi
                     */

                    $("#first_name").val(data.first_name);
                    $("#last_name").val(data.last_name);
                    $("#atesia").val(data.atesia);
                    //Convert default sql date to DD/MM/YYYY
                    var date = data.date.split('-').reverse().join('/');
                    $("#date").val(date);

                    $("#email").val(data.email);
                    $("#phone_number").val(data.phone_number);
                    $("#edit_form_image").attr('src', "../_photos/" + data.image_name)
                    $("#dw").attr('href', "../_photos/" + data.image_name)

                    //Selektojme radio butonin ne baze te rolit
                    var role = data.role;
                    if (role === "1") {
                        $("#roleAdmin").prop("checked", true);
                    } else {
                        $("#roleUser").prop("checked", true);
                    }


                }
            });
        });
    }




})








