$(function () {

    $('#login_btn').on('click', function (event) {
        event.preventDefault();
        var email = $('#email').val();

        var password = $('#password').val();

        //bejme check nese fushat jane bosh
        if (email === "" || password === "") {

            Swal.fire({title: 'Please enter email & password!', timer: 1400});
        } else {

            //i dergojme backendit te dhenat
            $.ajax({
                url: "ajax.php", method: "POST", data: {
                    action: 'login', email: email, password: password
                }, cache: false,

                success: function (response) {

                    if (response.indexOf('success') >= 0) {

                        window.location.href = '../profile/index.php';

                    } else {
                        Swal.fire('Failed!', 'Please try again!', 'error',)
                    }
                }, dataType: 'text'
            });
        }
    });


});


