$(function() {
   //here we save the opened tbl2 rows in array
   //so when user filters date, it auto opens them
   var openedTables = new Set();
   /**
    * Bejme inicializimin e tabeles kryesore
    */
   var tbl = $('#checkins_list').DataTable({
      processing: true,
      serverSide: true,
      ordering: true,
      pageLength: 10,
      lengthMenu: [5, 10, 20],
      ajax:main_tbl_options,
      columns: [{
         class: 'details-control1', data: 'user_id',
         render: function (dataField) { return `<i class='fas fa-plus-circle text-success' ${dataField} style='font-size:25px' ></i>`; }
      }, {
         data: 'first_name'
      }, {
         data: 'nr_dates', orderable: false
      }, {
         data: 'normal_hours', orderable: false
      }, {
         data: 'overtime', orderable: false
      }, {
         data: 'total_hours_in', orderable: false
         //Edit data after AJAX
      }]
   });

   searchFix_openedTables(tbl,openedTables)


   $('#checkins_list tbody').on('click', 'tr td.details-control1', function(event) {
      var tr = $(this).closest('tr');
      var row = tbl.row(tr);
      var index = parseInt(row[0]) + 1;

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

         initialize_table_2(row.data().row_details);

         tr.find('.fas').attr('class', 'fas fa-minus-circle fa-lg text-danger');
      }
   });

   function format_tbl2_html() {
      /**
       * Bejme draw tabelen e meposhte
       */
      return `
                <table class='innerTable tableclass2 display' style='width:100%'>
                   <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th class='text-right'>Date</th>
                            <th class='text-right'>Check in count</th>
                            <th class='text-right'>Normal hours</th>
                            <th class='text-right'>Overtime</th>
                            <th class='text-right'>Total/Date</th>
                        </tr>
                    </thead>
                </table>
`;
   }

   /**
    * Inicializojme tabelen e dyte
    */
   function initialize_table_2(object) {

      //inicializojme tabelen dytesore
      window.tbl2 = $(`.tableclass2`).DataTable({
         pageLength: 5,
         lengthMenu: [5, 20, 50, 75, 100],
         retrieve: true,
         paging: true,
         searching: true,
         info: true,
         data: object,
         columns: [{
            className: 'details-control2 ',
            orderable: false,
            data: null,
            width: '12%',
            defaultContent: '<i class="fas fa-plus-circle fa-lg text-dark" style="font-size:25px" aria-hidden="true"></i>'
         }, {
            className: 'dt-body-right', data: 'user_id'
         }, {
            className: 'dt-body-right', data: 'check_in_date'
         }, {
            className: 'dt-body-right', data: 'count'
         }, {
            className: 'dt-body-right', data: 'normal_hours'
         }, {
            className: 'dt-body-right', data: 'overtime'
         }, {
            className: 'dt-body-right', data: 'hours_per_date'
         }],
         columnDefs: [{
            targets: [1], visible: false, searchable: true, width: '0%'
         }]
      });

      second_table_details_control(tbl2);
   }

   function second_table_details_control(tbl2) {
      $('.tableclass2 tbody').on('click', 'td.details-control2', function(event) {
         var tr = $(this).closest('tr');

         var row = tbl2.row(tr);

         if (row.child.isShown()) {
            // Nese rresht eshte i hapur, e mbyllim
            row.child.hide();

            //Ndryshim ikone ne hide
            tr.find('.fa-minus-circle').attr('class', 'fas fa-plus-circle fa-lg text-dark');
         } else {
            // initialize_table_3(user_id);

            row.child(format_tbl3_html()).show();

            initialize_table_3(row.data().row_details);

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
        <th>Date in</th>
        <th>Check in</th>
        <th>Check out</th>
        <th>Date out</th>
    </tr>
</thead>
</table>`;
   }

   function initialize_table_3(data) {

      $(`.tableclass3`).DataTable({
         pageLength: 5,
         lengthMenu: [5, 20, 50, 75, 100],
         retrieve: true,
         paging: false,
         searching: false,
         info: false,
         data: data,
         createdRow: function(row, data, dataIndex) {
            if (data['pay_per_checkin']) {
               $(row).children('td').eq(4).html(`$ ${data['pay_per_checkin']}`);
            }
         },
         columns: [{
            data: 'check_in_date'
         }, {
            data: 'check_in_hour'
         }, {
            data: 'check_out_hour'
         }, {
            data: 'check_out_date'
         }]
      });
   }


});
