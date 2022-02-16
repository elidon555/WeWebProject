$(function() {

   /**
    * Change the icon
    */
   $('.table-responsive').on('click', '.fas', function() {

      $(this).toggleClass('fa-plus-circle fa-minus-circle');

      previousWidth = $(this).closest('td').width();
      console.log(previousWidth);


      //na duhet indexi, cdo row e ka, psh i j k
      let element = $(this).closest("tr").next('tr')
      if ( element.is(':hidden')){

         element.show()
      } else {
         element.hide();
      }

      console.log($(element).children().children().children()[0]);
      console.log($(element).find('td')[0]);


   });


   let date_s = $('.daterange');
   let startDate = date_s.data('daterangepicker').startDate.format('YYYY-MM-DD');
   let endDate = date_s.data('daterangepicker').endDate.format('YYYY-MM-DD');


   console.log('ajax');
   $.ajax({
      url: 'ajax.php',
      method: 'POST',
      dataType: 'json',
      data: {
         action: 'load_table',
         startDate: startDate,
         endDate: endDate
      },
      cache: false,
      success: function(data) {

         var i, j, z, zh;
         var user_data = '';
         for (i = 0; i < data.length; i++) {

            var month = data[i].row_details;
            var month_data = '';
            for (j = 0; j < month.length; j++) {


               var week = month[j].row_details;
               var week_data = '';
               for (z = 0; z < week.length; z++) {

                  var day = week[z].row_details;
                  var day_data = '';
                  for (zh = 0; zh < day.length; zh++) {

                     day_data += (`
                         <tr>
                            <td style='border-right: 2px solid red' scope='row'>${day[zh].date}</td>
                            <td class='border-right'>${day[zh].normal_hours_holiday}</td>
                            <td class='border-right'>${day[zh].normal_hours_weekend}</td>
                            <td class='border-right'>${day[zh].normal_hours_normal}</td>
                            <td style='border-right: 2px solid red'>${day[zh].normal_hours}</td>
                            <td class='border-right'>${day[zh].overtime_hours_holiday}</td>
                            <td class='border-right'>${day[zh].overtime_hours_weekend}</td>
                            <td class='border-right'>${day[zh].overtime_hours_normal}</td>
                            <td style='border-right: 2px solid red'>${day[zh].overtime}</td>
                            <td class='border-right'>${day[zh].normal_salary_holiday}</td>
                            <td class='border-right'>${day[zh].normal_salary_weekend}</td>
                            <td class='border-right'>${day[zh].normal_salary_normal}</td>
                            <td style='border-right: 2px solid red'>${day[zh].normal_salary}</td>
                            <td class='border-right'>${day[zh].overtime_salary_holiday}</td>
                            <td class='border-right'>${day[zh].overtime_salary_weekend}</td>
                            <td class='border-right'>${day[zh].overtime_salary_normal}</td>
                            <td style='border-right: 2px solid red'>${day[zh].overtime_salary}</td>
                        </tr>
               `);
                  }

                  week_data += (`
                         <tr >
                            <td style='border-right: 2px solid red' scope='row'>
                            <i index='day' z='${z}'  class='fas fa-plus-circle fa-lg text-dark ' style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i>${week[z].date}</td>
                            <td class='border-right'>${week[z].normal_hours_holiday}</td>
                            <td class='border-right'>${week[z].normal_hours_weekend}</td>
                            <td class='border-right'>${week[z].normal_hours_normal}</td>
                            <td style='border-right: 2px solid red'>${week[z].normal_hours}</td>
                            <td class='border-right'>${week[z].overtime_hours_holiday}</td>
                            <td class='border-right'>${week[z].overtime_hours_weekend}</td>
                            <td class='border-right'>${week[z].overtime_hours_normal}</td>
                            <td style='border-right: 2px solid red'>${week[z].overtime}</td>
                            <td class='border-right'>${week[z].normal_salary_holiday}</td>
                            <td class='border-right'>${week[z].normal_salary_weekend}</td>
                            <td class='border-right'>${week[z].normal_salary_normal}</td>
                            <td style='border-right: 2px solid red'>${week[z].normal_salary}</td>
                            <td class='border-right'>${week[z].overtime_salary_holiday}</td>
                            <td class='border-right'>${week[z].overtime_salary_weekend}</td>
                            <td class='border-right'>${week[z].overtime_salary_normal}</td>
                            <td style='border-right: 2px solid red'>${week[z].overtime_salary}</td>
                        </tr>
                          <tr class='day'><td class="p-0 m-0" colspan='18'><table class='table'>${day_data}</table></td></tr>
               `);
               }


               month_data += (`
                         <tr >
                            <td style='border-right: 2px solid red' scope='row'>
                            <i index='week' class='fas fa-plus-circle fa-lg text-dark ' style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i>${month[j].date}</td>
                            <td class='border-right'>${month[j].normal_hours_holiday}</td>
                            <td class='border-right'>${month[j].normal_hours_weekend}</td>
                            <td class='border-right'>${month[j].normal_hours_normal}</td>
                            <td style='border-right: 2px solid red'>${month[j].normal_hours}</td>
                            <td class='border-right'>${month[j].overtime_hours_holiday}</td>
                            <td class='border-right'>${month[j].overtime_hours_weekend}</td>
                            <td class='border-right'>${month[j].overtime_hours_normal}</td>
                            <td style='border-right: 2px solid red'>${month[j].overtime}</td>
                            <td class='border-right'>${month[j].normal_salary_holiday}</td>
                            <td class='border-right'>${month[j].normal_salary_weekend}</td>
                            <td class='border-right'>${month[j].normal_salary_normal}</td>
                            <td style='border-right: 2px solid red'>${month[j].normal_salary}</td>
                            <td class='border-right'>${month[j].overtime_salary_holiday}</td>
                            <td class='border-right'>${month[j].overtime_salary_weekend}</td>
                            <td class='border-right'>${month[j].overtime_salary_normal}</td>
                            <td style='border-right: 2px solid red'>${month[j].overtime_salary}</td>
                        </tr>
                            <tr class='week'><td class="p-0 m-0" colspan='18'><table class='table'>${week_data}</table></td></tr>
               `);
            }
            user_data += (`
                        <tr>
                            <td style='border-right: 2px solid red' scope='col' colspan='2'>
                            <i index='month' class='fas fa-plus-circle fa-lg text-dark ' style='font-size:25px;cursor: pointer;
                            user-select: none;' aria-hidden='true'></i> ${data[i].first_name}</td>
                            <td class='border-right'>${data[i].normal_hours_holiday}</td>
                            <td class='border-right'>${data[i].normal_hours_weekend}</td>
                            <td class='border-right'>${data[i].normal_hours_normal}</td>
                            <td style='border-right: 2px solid red'>${data[i].normal_hours}</td>
                            <td class='border-right'>${data[i].overtime_hours_holiday}</td>
                            <td class='border-right'>${data[i].overtime_hours_weekend}</td>
                            <td class='border-right'>${data[i].overtime_hours_normal}</td>
                            <td style='border-right: 2px solid red'>${data[i].overtime}</td>
                            <td class='border-right'>${data[i].normal_salary_holiday}</td>
                            <td class='border-right'>${data[i].normal_salary_weekend}</td>
                            <td class='border-right'>${data[i].normal_salary_normal}</td>
                            <td style='border-right: 2px solid red'>${data[i].normal_salary}</td>
                            <td class='border-right'>${data[i].overtime_salary_holiday}</td>
                            <td class='border-right'>${data[i].overtime_salary_weekend}</td>
                            <td class='border-right'>${data[i].overtime_salary_normal}</td>
                            <td style='border-right: 2px solid red'>${data[i].overtime_salary}</td>
                        </tr>
                        <tr class='month'><td class="p-0 m-0" colspan='18'><table class='table'>${month_data}</table></td></tr>
                        
               `);
         }

         var table = (`
               
                        <thead>
                        
                        <tr>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' rowspan='2' colspan='2' >Full Name</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='4'>Hours In</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='4'>Hours Out</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='4'>Payment In</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='4'>Payment Out</th>
                        </tr>
                        
                         <tr>
                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='1'>Total</th>
                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='1'>Total</th>
                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='1'>Total</th>
                            <th class='border-right' scope='col' colspan='1'>Holiday</th>
                            <th class='border-right' scope='col' colspan='1'>Weekend</th>
                            <th class='border-right' scope='col' colspan='1'>Normal</th>
                            <th style='vertical-align : middle;text-align:center;border-right: 2px solid red' scope='col' colspan='1'>Total</th>
                         <tr>
                           
                        </thead>
                        
                        <tbody>
                            ${user_data}
                        </tbody>
                       
         `);


         $('.table').html(table);
         $('.month').hide();
         $('.week').hide();
         $('.day').hide();


      }
   });


})
;
