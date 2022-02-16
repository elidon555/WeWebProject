$(function() {
   window.edit=0;

full_check();


$('#submitButton').on('click',function(event){
   event.preventDefault();

   let error=validate_submit();

   error -= 3;
   console.log(error);
   if (error !== 0) {
      Swal.fire('Error', 'Please fill in the details correctly', 'error');
      return false;
   }


   var email = $('#Email').val();

   var password = $('#Password').val();

   var confirm_password = $('#Confirm_password').val();

   //bejme check nese fushat jane bosh

   //i dergojme backendit te dhenat
   $.ajax({
      url: 'ajax.php',
      method: 'POST',
      data: {
         action: 'reset',
         email: email,
         password: password,
         confirm_password:confirm_password
      },
      cache: false,
      success: function(response) {
         var res = JSON.parse(response);
         if (res.status === 200) {
            Swal.fire('Success!', res.message, 'success').then(function() {
               window.location = "../login";
            });
         } else {
            Swal.fire('Failed!', res.message, 'error');
         }
      },
      dataType: 'text'
   });
})



});