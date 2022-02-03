$(function () {
    window.edit = 1;

   single_date_picker()

    /**
     * Definojme regex-at per fushat me poshte
     */

    /**
     * Bejme check vlerat e fushes ne eventin focus out
     */
    full_check();


    $("#signUp").on('submit', function (event) {
        event.preventDefault();

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
        var error = 0;

        /**
         * Validimi i te dhenave
         */

        //Ekzekutojme funksionin qe na validon te gjitha fushat
        //dhe ben return numrin e erroreve qe ka secila fushe
        error = validate_submit();


        //Nese nuk kemi errore,procedojme
        if (error === 0) {

            //Postojme te dhenat ne backend
            $.ajax({
                url: "ajax.php", method: "POST", data: {
                    signup: 1,
                    terms: '1',
                    first_name: first_name,
                    last_name: last_name,
                    atesia: atesia,
                    date: date,
                    email: email,
                    phone_number: phone_number,
                    password: password,
                    confirm_password: confirm_password,
                }, cache: false, dataType: 'json', success: function (res) {
                    var response = res;

                    //I tregojme userit mesazhet e backend-it
                    if (response.status != 200) {
                        Swal.fire('Error!', response['message'], 'error',)
                    } else {
                        Swal.fire('Success!', 'Your account registered successfully', 'success',)
                        setTimeout(function () {
                            window.location = "../login/index.php";
                        }, 1500);
                    }
                },
            });
        } else {
            Swal.fire('Error!', 'Please fill the fields as required', 'error',)
        }
    })


})