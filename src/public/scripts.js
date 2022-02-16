function isEmpty(value) {
    return (
        value === null || // check for null
        value === undefined || // check for undefined
        value === '' || // check for empty string
        (Array.isArray(value) && value.length === 0) || // check for empty array
        (typeof value === 'object' && Object.keys(value).length === 0) // check for empty object
    );
}

function delay(callback, ms) {
    var timer = 0;
    return function() {
        var context = this,
            args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function() {
            callback.apply(context, args);
        }, ms || 0);
    };
}

//Search delay,open previously opened tables when applying date filter
function searchFix_openedTables(tbl,openedTables){
    /**
     * Start of search delay
     */
    var search_s = $('div.dataTables_filter input');
    search_s.unbind();

    search_s.on('keyup', delay(function(event) {
        openedTables.clear();
        tbl.search(this.value).draw();
    }, 500));
    /**
     * End of search delay
     */

    /**
     * Clears opened tables array which auto showed all previously opened tables
     */
    tbl.on('page.dt', function() {
        openedTables.clear();
    });

    $('#applyFilter').on('click', function() {
        tbl.draw();
        setTimeout(function() {
            for (let item of openedTables.values()) $('.details-control1').eq(item).trigger('click');
        }, 200);
    });
}

var main_tbl_options =  {
    data: function(data) {
        data.action = 'load_table';
        //Ne momentin qe bejme load tabelen, backendi merr diten e sotme minus 30 dite

        var date_s = $('.daterange');
        var startDate = date_s
           .data('daterangepicker')
           .startDate.format('YYYY-MM-DD');
        var endDate = date_s
           .data('daterangepicker')
           .endDate.format('YYYY-MM-DD');
        data.startDate = startDate;
        data.endDate = endDate;
    }, url: 'ajax.php', type: 'post'
}

/**
 * Marrim vleren e biskotes
 * @param cookiename
 * @returns {string}
 */
function getCook(cookiename) {
    // Get name followed by anything except a semicolon
    var cookiestring = RegExp(cookiename + '=[^;]+').exec(document.cookie);
    // Return everything after the equal sign, or an empty string if the cookie name not found
    return decodeURIComponent(!!cookiestring ? cookiestring.toString().replace(/^[^=]+./, '') : '');
}


const letters_pattern = /^[a-zA-Z ]+$/;
const phone_pattern = /^[0-9]*$/;
const email_pattern = /^\w+([-+.'][^\s]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
const password_pattern =
    /^(?=.*[0-9])(?=.*[!@#$%^&*\.])[a-zA-Z0-9!@#$%^&*\.]{8,100}$/;

function full_check() {
    check_validation(letters_pattern, 'First_name');
    check_validation(letters_pattern, 'Last_name');
    check_validation(letters_pattern, 'Atesia');
    check_validation(null, 'Date');
    check_validation(email_pattern, 'Email');
    check_validation(phone_pattern, 'Phone_number');
    check_validation(password_pattern, 'Password');
    check_validation(null, 'Confirm_password');
    check_validation(null, 'Terms');
}

function check_validation(pattern, string) {
    var new_string = string.charAt(0).toLowerCase() + string.slice(1);

    $(`#${string}, #${new_string}`).on('focusout', function(event) {
        if (window.edit === 1) {
            check(pattern, new_string);
        } else {
            check(pattern, string);
        }
    });
}

function single_date_picker() {
    $('input[name="date"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        minYear: 1901,
        maxYear: parseInt(moment().format('YYYY'), 10),

        locale: {
            format: 'DD/MM/YYYY'
        }
    });
}
/**
 * Validon te dhenat e userit ne front-end
 * @returns {number}
 */
function validate_submit() {
    /**
     * Validimi i te dhenave
     */
    var error = 0;

    // validimi i emrit
    if (check(letters_pattern, 'First_name')) error++;

    // validimi i mbiemrit
    if (check(letters_pattern, 'Last_name')) error++;

    //validimi i atesise
    if (check(letters_pattern, 'Atesia')) error++;

    // validimi i emailit
    if (check(email_pattern, 'Email')) error++;

    // validimi i numrit
    if (check(phone_pattern, 'Phone_number')) error++;

    // validimi i date se lindjes
    if (check(null, 'Date')) error++;

    //validimi i passwordit
    if (check(password_pattern, 'Password')) error++;

    //validimi i konfirmimit te passwordit
    if (check(null, 'Confirm_password')) error++;

    if (check(null, 'terms')) error++;

    return error;
}

/**
 * Validimi i nje imazhi
 * @param file
 * @returns {boolean|number}
 */
function image_check(file) {
    var error = 0;

    if (file === undefined) {
        return false;
    }

    if (file['size'] === 0) {
        swal.fire('Failed!', 'File is required', 'error');
        console.log('file has 0kb');
        return error++;
    }

    var splited_name = file['name'].split('.');
    var file_extension = splited_name[splited_name.length - 1];
    var file_extension_list = ['png', 'jpg', 'jpeg'];
    var size = file['size'];

    if (!file_extension_list.includes(file_extension)) {
        swal.fire('Failed!', 'File format is not correct', 'error');
        error++;
    } else if (size >= 5242880) {
        swal.fire('Failed!', 'File must be less than 5 Megabytes', 'error');
        error++;
    }
    return error;
}

function check(pattern, string) {
    if (string === 'terms') {
        if ($(`#terms`).length == 0) return false;
        if ($(`#terms`).is(':checked')) {
            $(`#terms_error_message`).hide();
            return false;
        } else {
            handle_error(string, '<br>Please agree to all terms of services!');
            return true;
        }
    }

    var new_string = string.charAt(0).toLowerCase() + string.slice(1);
    if (window.edit === 1) {
        string = new_string;
    }

    if (window.edit === 1) {
        var arrayValueCheck = [
            'date',
            'confirm_password',
            'email',
            'phone_number',
            'password'
        ];
    } else {
        arrayValueCheck = [
            'Date',
            'Confirm_password',
            'Email',
            'Phone_number',
            'Password'
        ];
    }

    if (string === arrayValueCheck[0]) {
        /**
         * Validimi i dates
         */
        var date_s = $(`#${string}`);
        var dateSelected = moment(date_s.val(), 'DD/MM/YYYY');

        var year = moment(date_s.val(), 'DD/MM/YYYY').year();
        var month = moment(date_s.val(), 'DD/MM/YYYY').month();
        var date = moment(date_s.val(), 'DD/MM/YYYY').date();

        var currentDate = Date.now();
        var difference = currentDate - dateSelected;

        if (difference > 567650000000) {
            if (year > 1900 && month < 12 && date < 31) {
                handle_success(string);
                return false;
            } else {
                handle_error(string, 'Invalid Date!');
            }
        } else {
            handle_error(string, ' Must be over 18 to register!');
            return true;
        }

        /**
         * Validimi i confirm,password
         */
    } else if (string === arrayValueCheck[1]) {
        var password = $(`#${arrayValueCheck[4]}`).val();
        var confirm_password = $(`#${string}`).val();

        if (password !== confirm_password) {
            handle_error(string, 'Password doesn\'t match!');
            return true;
        } else {
            handle_success(string);
            return false;
        }
    } else {
        /**
         * Validimi i passwordit
         */
        let string_value = $(`#${string}`).val();
        password = $(`#${arrayValueCheck[4]}`).val();

        //allowing empty password on edit form
        if (string === 'password' && string_value === '') {
            handle_success(string);
            return false;
        }

        //Validimi i pergjithshem i patternave
        if (pattern.test(string_value) && string_value !== '') {
            handle_success(string);
            return false;
        } else {
            /**
             * Mesazhet e errorit
             */
            if (string === arrayValueCheck[2]) {
                handle_error(string, 'Wrong email format! ');
            } else if (string === arrayValueCheck[3]) {
                handle_error(string, 'Wrong phone number format! ');
            } else if (string === 'Password') {
                handle_error(
                    string,
                    'At least 1 uppercase,lowercase,number and symbol'
                );
            } else if (string === 'password' && password !== '') {
                handle_error(
                    string,
                    'At least 1 uppercase,lowercase,number and symbol'
                );
            } else {
                handle_error(string, 'Must contain only characters ');
            }
            return true;
        }
    }
}

//bejme handle mesazhet e errorit
function handle_error(string, message) {
    let error = $(`#${string}_error_message`);
    error.html(message);
    error.show();
    $(`#${string}`).css('border-bottom', '2px solid #F90A0A');
}

//bejme handle mesazhet e suksesit
function handle_success(string) {
    $(`#${string}_error_message`).hide();
    $(`#${string}`).css('border-bottom', '2px solid #34F458');
}

// inicializimi i daterange
// daterange
var start = moment().subtract(29, 'days');
var end = moment();

function cb(start, end) {
    $('.daterange span').html(
        start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD')
    );
}

$('.daterange').daterangepicker(
    {
        startDate: start,
        endDate: end,
        format: 'YYYY/MM/DD',
        separator: ' - ',
        ranges: {
            Today: [moment(), moment()],
            Yesterday: [
                moment().subtract(1, 'days'),
                moment().subtract(1, 'days')
            ],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [
                moment().subtract(1, 'month').startOf('month'),
                moment().subtract(1, 'month').endOf('month')
            ]
        },
        locale: {
            cancelLabel: 'Clear'
        }
    },
    function(start, end) {
        $('.daterange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        window.startDate = start;
        endDate = end;

    }
);
