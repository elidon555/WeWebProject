$(function() {

   //here we save the opened inner_table rows in array
   //so when user filters date, it auto opens them
   let openedTables = new Set();
   let normal_hours_details;
   let table;
   let table_s = $('.table_class1');


   /**
    * Bejme inicializimin e tabeles kryesore
    */
   table = table_s.DataTable({
      searchDelay: 400,
      processing: true,
      serverSide: true,
      ordering: true,
      pageLength: 10,
      lengthMenu: [5, 10, 20],
      ajax: main_tbl_options,
      'drawCallback': function(settings) {
         normal_hours_details = settings.json['aaData'].map(x => x.normal_hours_description);
      }, columns: [{
         class: 'details-control1',
         data: null,
         defaultContent: '<i class="fas fa-plus-circle fa-lg" style="font-size:25px" aria-hidden="true"></i>' },
         {
         data: 'first_name'
      }, {
         data: 'nr_dates', orderable: false
      }, {
         data: 'normal_hours', orderable: false, className: 'hours_details'
      }, {
         data: 'normal_salary', orderable: false
      }, {
         data: 'overtime', orderable: false
      }, {
         data: 'overtime_salary', orderable: false
      }, {
         data: 'total_hours', orderable: false
      }, {
         data: 'salary_hour', orderable: false
      }, {
         data: 'salary', orderable: false
      }]
      /**
       * When clickingnormal hours column, do this
       */
   });
   table_s.on('click', '.hours_details', function() {
      //Get index
      let index = $(this).closest('tr').index();
      //Get cookie
      let hours_details = $('#hour_details');
      //Change modal body template
      hours_details.find('.modal-body').html(`<p>${normal_hours_details[index]}</p>`);
      //Open it
      hours_details.modal('toggle');
   });

   /**
    * Add delay on search
    * Store previously opened tables on date search
    */
   searchFix_openedTables(table, openedTables);

   table_detail_controls(table, 1);

   function format_tbl_html(i) {
      /**
       * Bejme draw tabelen e meposhte
       */
      return `
                <table class='innerTable table_class${i} display' style='width:100%'>
                   <thead>
                        <tr>
                          <th></th>
                            <th class='text-right'>Date</th>
                            <th class='text-right'>Normal Hours</th>
                            <th class='text-right'>Normal Salary</th>
                            <th class='text-right'>Overtime Hours</th>
                            <th class='text-right'>Overtime Salary</th>
                            <th class='text-right'>Total Hours</th>
                            <th class='text-right'>Pay/Hour</th>
                            <th class='text-right'>Salary/week</th>
                        </tr>
                    </thead>
                </table>
`;
   }

   /**
    * Inicializojme tabelen e dyte
    */
   function initialize_table(data, i) {

      //Ruajme te dhena me id-ne e userit si key
      var bool;
      (i === 4) ? bool = false : bool = true;
      //inicializojme tabelen dytesore
      let inner_table = $(`.table_class${i}`).DataTable({
         pageLength: 5,
         lengthMenu: [5, 20, 50, 75, 100],
         retrieve: true,
         paging: bool,
         searching: bool,
         info: bool,
         data: data, columns:
            [{
               className: `details-control${i}`,
               orderable: false,
               data: null,
               width: '1%',
               defaultContent: '<i class="fas fa-plus-circle fa-lg text-dark" style="font-size:25px" aria-hidden="true"></i>'
            }, {
               data: 'date'
            }, {
               data: 'normal_hours'
            }, {
               data: 'normal_salary'
            }, {
               data: 'overtime'
            }, {
               data: 'overtime_salary'
            }, {
               data: 'total_hours'
            }, {
               data: 'salary_hour'
            }, {
               data: 'salary'
            }]
      });
      //Hide latest index's column
      $(`.details-control4`).hide();
      table_detail_controls(inner_table, i);
   }

   function table_detail_controls(inner_table, i) {
      $(`.table_class${i} tbody`).on('click', `td.details-control${i}`, function(event) {

         let tr = $(this).closest('tr');
         let row = inner_table.row(tr);
         let index = parseInt(row[0]) + 1;

         if (row.child.isShown()) {

            // Nese rresht eshte i hapur, e mbyllim
            row.child.hide();

            //Ndryshim ikone ne hide
            tr.find('.fa-minus-circle').attr('class', 'fas fa-plus-circle fa-lg text-dark');
            if (i === 1) {
               openedTables.delete(index + 1);
            }
         }
         //If hidden, show it
         else {
            if (i === 1) {
               openedTables.add(index + 1);
            }
            // initialize_table_3(user_id);
            row.child(format_tbl_html(i + 1)).show();

            initialize_table(row.data().row_details, i + 1);

            //Ndryshim ikone ne show
            tr.find('.fa-plus-circle').attr('class', 'fas fa-minus-circle fa-lg text-dark');
         }
      });
   }

});
