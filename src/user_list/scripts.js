function create_edit_user() {
   /**
    * Marrim te henat qe ka plotesuar perdoruesi
    */
   if (window.edit == 0) {
      var first_name = $('#First_name').val()
      var last_name = $('#Last_name').val()
      var atesia = $('#Atesia').val()
      var date = $('#Date').val()
      var email = $('#Email').val()
      var phone_number = $('#Phone_number').val()
      var password = $('#Password').val()
      var confirm_password = $('#Confirm_password').val()
      var files = $('#File')[0].files[0]
      var role = $('#roleUser').is(':checked') ? 'User' : 'Admin'
      var data = new FormData()
   } else {
      first_name = $('#first_name').val()
      last_name = $('#last_name').val()
      atesia = $('#atesia').val()
      date = $('#date').val()
      email = $('#email').val()
      phone_number = $('#phone_number').val()
      password = $('#password').val()
      confirm_password = $('#confirm_password').val()
      files = $('#file')[0].files[0]
      role = $('#roleUser').is(':checked') ? 'User' : 'Admin'
      data = new FormData()
   }

   /**
    * Validimi i te dhenave
    */
   //Bejme validate te gjitha fushat
   var error = validate_submit()

   //Bejme validate foton nese useri e shton
   error += image_check(files)

   /**
    * Nese ka error shfaq mesazh error
    */
   if (error > 0) {
      Swal.fire('Please enter all the fields correctly!', '', 'error')
      return false
   }

   //Nese nuk kemi errore, procedojme

   /////////

   /**
    * Ruajme te dhenat ne form data
    */
   data.append('file', files)
   data.append('action', 'update||delete')
   if (window.edit === 1) {
      data.append('id', window.id)
   }
   data.append('first_name', first_name)
   data.append('last_name', last_name)
   data.append('atesia', atesia)
   data.append('date', date)
   data.append('email', email)
   data.append('phone_number', phone_number)
   data.append('password', password)
   data.append('confirm_password', confirm_password)
   data.append('role', role)
   /**
    * I dergojme backend-it te gjitha te dhenat
    */
   $.ajax({
      url: '../public/functions.php',
      method: 'POST',
      data: data,
      contentType: false,
      processData: false,
      cache: false,
      success: function (response) {
         response = JSON.parse(response)

         if (response.status != 200) {
            Swal.fire('Error!', response.message, 'error')
         } else {
            Swal.fire('Success!', response.message, 'success')
            $('.modal-footer >button:first-child').click()
            $('#loadAfterAction').click()
         }
      },
      dataType: 'text',
   })
}

$(function () {

   /**
    * select2 Filters multiple support, scalable
    */
   $('.filter').select2({
      placeholder: 'Select an item',
      minimumInputLength: 0,
      ajax: {
         url: 'ajax.php',
         dataType: 'json',
         delay: 250,
         data: function (params) {
            var index = $('.filter').index(this)

            if (index == 0) {
               email = params.term
            } else {
               var email = $('.filter').eq(0).select2('data')


               if (isEmpty(email)) {
                  email = ''
               } else {
                  email=email[0].text
               }
            }

            if (index == 1) {
               phone_number = params.term
            } else {
               var phone_number = $('.filter').eq(1).select2('data')

               if (isEmpty(phone_number)) {
                  phone_number = ''
               } else {
                  phone_number=phone_number[0].text
               }
            }

            return {
               email: email,
               phone_number: phone_number,
               action: 'select2Filter',
               type: 'public',
               name: $(this).attr('name'),
            }
         },
         processResults: function (response) {
            return {
               results: response,
            }
         },
         cache: true,
      },
   })

   /**
    * Bejme inicializimin e tabeles user_list
    */
   var table = $('#user_list').DataTable({
      processing: true,
      serverSide: true,
      ordering: true,
      orderCellsTop: true,
      fixedHeader: true,
      ajax: {
         url: 'ajax.php',
         type: 'post',
         data: function (data) {
            data.action = 'load_table'

            //Ne momentin qe bejme load tabelen, backendi merr diten e sotme minus 30 dite

            /**
             * Mbasi behet draw tabela te pakten njehere, tani mund te aplikojme filtrin e dates
             */
            if (window.allowFilter == 1) {

               var email = $('.filter').eq(0).select2('data')
               var phone_number = $('.filter').eq(1).select2('data')
               var dateValue = $('.daterange').val()



               if (isEmpty(email)) email=""; else email = email[0].text;
               if (isEmpty(phone_number)) phone_number=""; else phone_number=phone_number[0].text


               if (dateValue!="") {
                  var startDate = $('.daterange')
                      .data('daterangepicker')
                      .startDate.format('YYYY-MM-DD')

                  var endDate = $('.daterange')
                      .data('daterangepicker')
                      .endDate.format('YYYY-MM-DD')
               }
               console.log(endDate)

               data.email = email
               data.phone_number = phone_number
               data.startDate = startDate
               data.endDate = endDate
            }
         },
      },

      columns: [
         {
            data: 'user_id',
         },
         {
            data: 'image_name',
         },
         {
            data: 'first_name',
         },
         {
            data: 'last_name',
         },
         {
            data: 'atesia',
         },
         {
            data: 'date_of_birth',
         },
         {
            data: 'phone_number',
         },
         {
            data: 'actions',
         },
      ],

      createdRow: function (row, data, dataIndex) {
         /**
          * EDIT ROW DATA
          */
         if (data['image_name']) {
            //Use path to get image
            $(row)
               .children('td')
               .eq(1)
               .html(
                  `<img  height='50px' width='50px' src='../_photos/${data['image_name']}'>`
               )
         }

         if (data['actions']) {
            $(row).children('td').eq(7).html(`

            <button class='btn btn-primary btn-sm edit_btn' data-toggle='modal' data-target='#editModal' type='button' class='button-primary'
            value='${data['user_id']}'>View</button>
            
            <button class='btn btn-danger btn-sm delete'  type='button'  class='button-danger'
            value='${data['user_id']}'>Delete</button>
                `)
         }
      },
      initComplete: function (settings, json) {
         /**
          * Apply filter when clicking enter
          */
         var search_s = $('div.dataTables_filter input')
         search_s.unbind()

         search_s.on(
            'keyup',
            delay(function (event) {
               table.search(this.value).draw()
            }, 700)
         )

         window.allowFilter = 1
      },
   })

   /**
    * EDIT MODAL
    */

   var edit_modal_s = $('#editModal')
   edit_modal_s.on('shown.bs.modal', function () {
      window.edit = 1
      //Bejme load daterangepicker
      single_date_picker()
   })

   //Kur bejme hide edit modal, fshijme vleren e file input + bejme hide file preview
   edit_modal_s.on('hide.bs.modal', function () {
      document.getElementById('file').value = null
      $('#blah2').hide()
   })
   /**
    * END OF EDIT MODAL
    */

   /**
    * SIGNUP MODAL
    */
   var signup_modal_s = $('#signupModal')

   //Kur bejme hide signup modal, fshijme vleren e file input + bejme hide file preview
   signup_modal_s.on('hidden.bs.modal', function () {
      document.getElementById('File').value = null
      document.getElementById("formSignUp").reset();


      $('#blah').hide()
   })

   signup_modal_s.on('shown.bs.modal', function () {

      window.edit = 0
      //Bejme load daterangepicker
      single_date_picker()
   })

   $('#applyFilter').on('click', function () {

      table.draw()

   })

   $('#clearFilter').on('click', function () {

      $('.filter').val(null).trigger('change');
      $('.daterange').val('')
      table.draw()

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
   })

   //full validation_check real time
   full_check()

   // submit_add_user()

   function delete_user() {
      //confirm delete yes or no
      $('.delete').on('click', function () {
         //Marrim vleren e butonit i cili ka ID-ne
         var id = $(this).attr('value')

         swal
            .fire({
               title: 'Are you sure?',
               text: "You won't be able to revert this!",
               icon: 'warning',
               showCancelButton: true,
               confirmButtonColor: '#3085d6',
               cancelButtonColor: '#d33',
               confirmButtonText: 'Yes, delete it!',
            })
            .then((result) => {
               /* Read more about isConfirmed, isDenied below */
               if (result.isConfirmed) {
                  $.ajax({
                     url: 'ajax.php',
                     method: 'POST',
                     data: {
                        action: 'delete',
                        id: id,
                     },
                     cache: false,

                     success: function (response) {
                        response = JSON.parse(response)

                        if (response.status != 200) {
                           Swal.fire('Error!', response['message'], 'error')
                        } else {
                           Swal.fire('Success!', response['message'], 'success')
                        }
                     },
                  })
               } else {
                  Swal.fire('Action canceled!', '', 'info')
               }
            })
      })
   }

   function load_single_user_detail() {
      $('.edit_btn').on('click', function () {
         id = $(this).attr('value')
         console.log($(this))

         $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
               id: id,
               action: 'load_single_user',
            },
            cache: false,
            success: function (data) {
               data = JSON.parse(data)

               /**
                * Plotesojme formen me te gjitha te dhenat e marra nga backendi
                */

               $('#first_name').val(data.first_name)
               $('#last_name').val(data.last_name)
               $('#atesia').val(data.atesia)
               //Convert default sql date to DD/MM/YYYY
               var date = data.date.split('-').reverse().join('/')
               $('#date').val(date)

               $('#email').val(data.email)
               $('#phone_number').val(data.phone_number)
               $('#edit_form_image').attr(
                  'src',
                  '../_photos/' + data.image_name
               )
               $('#dw').attr('href', '../_photos/' + data.image_name)

               //Selektojme radio butonin ne baze te rolit
               var role = data.role
               console.log(role)
               if (role === '1') {
                  $('#roleAdmin').prop('checked', true)
               } else {
                  $('#roleUser').prop('checked', true)
               }
            },
         })
      })
   }
})
