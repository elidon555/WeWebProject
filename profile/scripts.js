$(function () {
    window.edit = 1;

    // Mundeson file upload ne klikim te fotos
    $("#loadedImage").click(function () {
        $("#file").click();
    });

   single_date_picker()

    /**
     * Definojme regex-at per fushat me poshte
     */

    full_check()
    edit_profile()
    edit_password()

    /**
     * Bejme check vlerat e fushes ne eventin focus out
     */


    function edit_profile() {
        $('#edit_profile_btn').on('click', function (event) {
            event.preventDefault();
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
            var files = $('#file')[0].files[0];
            console.log(files);

            /**
             * Validimi i te dhenave
             */

            error = validate_submit();

            error += image_check(files);


            if (error == 0) {

                /**
                 * Fusim te gjithe te dhenat ne nje FormGroup
                 */
                    //store all data below
                var data = new FormData();
                data.append('file', files);
                data.append('update', "1");
                data.append('first_name', first_name);
                data.append('last_name', last_name);
                data.append('atesia', atesia);
                data.append('date', date)
                data.append('email', email);
                data.append('phone_number', phone_number);

                $.ajax({
                    url: "ajax.php",
                    method: "POST",
                    data: data,
                    contentType: false,
                    processData: false,
                    cache: false,
                    success: function (response) {

                        response = JSON.parse(response)

                        if (response.status != 200) {
                            Swal.fire('Error!', response['message'], 'error',)
                        } else {
                            Swal.fire('Success!', response['message'], 'success',)

                        }
                    },
                    dataType: 'text'
                });

                return true;

            } else {
                Swal.fire('Please enter all the fields correctly!', '', 'error',)
                return false;
            }

        })
    }


    function edit_password() {
        $("#edit_password").on('click', function () {

            var error = 0

            //Bejme validate password
            if (check(password_pattern, "password")) error++
            //Bejme validate confirm password
            if (check(null, "confirm_password")) error++

            //Nese nuk kemi errore,procedojme
            if (error == 0) {


                /**
                 * Marrim te dhenat nga useri
                 */

                //Marrim passwordin e vjeter
                var old_password = $('#old_password').val();

                //Marrim passwordin e ri
                var password = $('#password').val();

                //Marrim confirmimin e passwordit
                var confirm_password = $('#confirm_password').val();


                //I cojme back-endit te dhenat
                $.ajax({
                    url: "ajax.php", method: "POST", data: {
                        edit_pwd: 1, old_password: old_password, password: password, confirm_password: confirm_password,
                    }, cache: false,

                    success: function (response) {
                        response = JSON.parse(response)

                        //I tregojm userit response-in me mesazh
                        if (response.status == 404) {
                            Swal.fire('Error!', response['message'], 'error',)
                        } else {
                            Swal.fire('Success!', 'Password successfully changed!', 'success',)
                        }
                    }
                });
                return true;
            } else {

                $('#old_password,#password,#confirm_password').val("")

                Swal.fire('Please enter all the fields correctly!', '', 'error',)

                return false;
            }

        })
    }


})