
$(function () {
   window.edit=1

   if (localStorage.getItem('logout')==='1'){
      Swal.fire('Success', 'You are logged out!', 'success');
      localStorage.removeItem('logout');
   }

   if (localStorage.getItem('signup')==='1'){
      Swal.fire('Success', 'Successfully registered!', 'success');
      localStorage.removeItem('signup');
   }

   full_check()

   $('#login_btn').on('click', function (event) {
      event.preventDefault();

      var error = validate_submit()

      //Since it validates all fields, we're missing some fields, so we substract by 3.
      error -= 3


      if (error!==0) {
         Swal.fire('Error', 'Please fill in the details correctly', 'error');
         return false;
      }

         var email = $('#email').val();

         var password = $('#password').val();

         //bejme check nese fushat jane bosh


         //i dergojme backendit te dhenat
         $.ajax({
            url: 'ajax.php',
            method: 'POST',
            data: {
               action: 'login',
               email: email,
               password: password
            },
            cache: false,

            success: function(response) {
               var res = JSON.parse(response)
               if (res.status == 200) {
                  window.location.href = '../profile/';
               } else {
                  Swal.fire('Failed!', res.message, 'error');
               }
            },
            dataType: 'text'
         });

   });
});
