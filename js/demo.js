$(document).ready(function(){

    //Get saved layout type from LocalStorage
    var token = localStorage.getItem('oaufacewars-token');
    if (token !== null) {
//                    $(location).attr('href', BASE_URL);
        $('#sign-in').attr('style', 'display: none');
    } else {
        $('#sign-out').attr('style', 'display: none');
    }

    //if (window.location.hostname == 'localhost') {
    //    var base_url = 'http://localhost/oaufacewars/';
    //} else {
    //    var base_url = 'http://oau-facewars.rhcloud.com/';
    //
    //}
    //var api_url = base_url+'api/v1';
    //localStorage.setItem('oaufacewars-base-url', base_url);
    //localStorage.setItem('oaufacewars-api-url', api_url);

    //Welcome Message (not for login page)
    function notify(message, type, align){
        $.growl({
            message: message
        },{
            type: type,
            allow_dismiss: false,
            label: 'Cancel',
            className: 'btn-xs btn-inverse',
            placement: {
                from: 'top',
                align: align
            },
            delay: 2500,
            animate: {
                enter: 'animated bounceIn',
                exit: 'animated bounceOut'
            },
            offset: {
                x: 20,
                y: 85
            }
        });
    };

    //if (!$('.login-content')[0]) {
    //    notify('Welcome back User', 'inverse');
    //}
    function register(email,password) {
        $.ajax({
            type: "POST",
            url: API_URL+"/register",
            timeout: 20000,
            data: "email="+email+"&password="+password,
            success: function(data){
                con = data;
                //console.log(con);
                if (con['error']) {
                    notify(con['message'],'danger', 'center');
                } else {
                    notify('Registration Successful: Check your inbox for verification code', 'success', 'center');
                }
            },
            error: function(xhr, desc, err) {
                //console.log(xhr);
                //console.log("|Details: " + desc + "|Error: " + err);
                if (xhr['responseJSON']['error']) {
                    notify(xhr['responseJSON']['message'],'danger', 'center');
                }
            }
        });
    }

    function login(email,password) {
        $.ajax({
            type: "POST",
            url: API_URL+"/login",
            timeout: 20000,
            data: "email="+email+"&password="+password,
            success: function(data){
                con = data;
                //console.log(con);
                //$("tbody#tablespace").html(con);
                //console.log(con);
                if (con['error']) {
                    notify(con['message'],'danger', 'center');
                } else {
                    notify('Successful Login', 'success', 'center');
                    localStorage.setItem('oaufacewars-token', con['token']);
                    $(location).attr('href', BASE_URL);
                }
            },
            error: function(xhr, desc, err) {
                //console.log(xhr['responseJSON']['message']);
                //console.log("|Details: " + desc + "|Error: " + err);
                if (xhr['responseJSON']['error']) {
                    notify(xhr['responseJSON']['message'],'danger', 'center');
                }

            }
        });
    }

    function verify(email,code) {
        $.ajax({
            type: "POST",
            url: API_URL+"/verify",
            timeout: 20000,
            data: "email="+email+"&code="+code,
            success: function(data){
                con = data;
                //console.log(con);
                //$("tbody#tablespace").html(con);
                //console.log(con);
                if (con['error']) {
                    notify(con['message'],'danger', 'center');
                } else {
                    notify(con['message'],'success', 'center');
                }
            },
            error: function(xhr, desc, err) {
                //console.log(xhr['responseJSON']['message']);
                //console.log("|Details: " + desc + "|Error: " + err);
                if (xhr['responseJSON']['error']) {
                    notify(xhr['responseJSON']['message'],'danger', 'center');
                }

            }
        });
    }

    function vote(competitor_id, token) {
        $.ajax({
            type: "POST",
            url: API_URL+"/vote",
            timeout: 20000,
            data: "competitor_id="+competitor_id+"&token="+token,
            success: function(data){
                con = data;
                //console.log(con);
                //$("tbody#tablespace").html(con);
                //console.log(con);
                if (con['error']) {
                    notify(con['message'],'danger', 'center');
                } else {
                    notify(con['message'],'success', 'center');
                    $('.c-footer').find('button').addClass('disabled');
                }
            },
            error: function(xhr, desc, err) {
                //console.log(xhr['responseJSON']['message']);
                //console.log("|Details: " + desc + "|Error: " + err);
                if (xhr['responseJSON']['error']) {
                    notify(xhr['responseJSON']['message'],'danger', 'center');
                }

            }
        });
    }

    function contact(token,name,email,message) {
        $.ajax({
            type: "POST",
            url: API_URL+"/contact",
            timeout: 20000,
            data: "token="+token+"&name="+name+"&email="+email+"&message="+message,
            success: function(data){
                con = data;
                //console.log(con);
                //$("tbody#tablespace").html(con);
                //console.log(con);
                if (con['error']) {
                    notify(con['message'],'danger', 'center');
                } else {
                    //notify(con['message'],'success', 'center');
                    swal("Thank you!!", "Your message has been posted successfully", "success");
                    $('body').find('#contact-name').val("");
                    $('body').find('#contact-email').val("");
                    $('body').find('#contact-message').val("");
                }
            },
            error: function(xhr, desc, err) {
                //console.log(xhr['responseJSON']['message']);
                //console.log("|Details: " + desc + "|Error: " + err);
                if (xhr['responseJSON']['error']) {
                    notify(xhr['responseJSON']['message'],'danger', 'center');
                }

            }
        });
    }



    $('body').on('click', '#register', function(e){
        e.preventDefault();
        //$(this).parent().addClass('toggled');
        var email = $('body').find('#register-email').val();
        var password = $('body').find('#register-password').val();
        var password_check = $('body').find('#register-password-check').val();
        if (email !== '' && password !== '' && password_check !== '') {
            if (password != password_check) {
                notify('Passwords do not match', 'danger', 'center');
            }else {
                register(email,password);
            }
        } else {
            notify('Email Address or Password not set', 'danger', 'center');
        }

    });

    $('body').on('click', '#login', function(e){
        e.preventDefault();
        //$(this).parent().addClass('toggled');
        var email = $('body').find('#login-email').val();
        var password = $('body').find('#login-password').val();
        if (email !== '' && password !== '') {
            login(email,password);

        } else {
            notify('Email Address or Password not set', 'danger', 'center');
        }
    });

    $('body').on('click', '#verify', function(e){
        e.preventDefault();
        //$(this).parent().addClass('toggled');
        var email = $('body').find('#verify-email').val();
        var code = $('body').find('#verify-code').val();
        if (email !== '' && code !== '') {
            verify(email,code);

        } else {
            notify('Email Address or Code not set', 'danger', 'center');
        }
    });

    $('body').on('click', '.vote', function(e){
        e.preventDefault();
        //$(this).parent().addClass('toggled');
        //var email = $('body').find('#verify-email').val();
        //var code = $('body').find('#verify-code').val();
        //if (email !== '' && code !== '') {
        //    verify(email,code);
        //
        //} else {
        var token = localStorage.getItem('oaufacewars-token');
        if (token === null) {
            notify('Login to Vote', 'inverse', 'center');
        } else {
            var competitor_id = $(this).attr('id');
            //console.log(competitor_id);
            vote(competitor_id,token);
        }
    });

    $('body').on('click', '#vote-one', function(e){
        e.preventDefault();

        notify('ok o', 'success', 'center');
        $('body').find('#today-date').text('trstt');
    });

    $('body').on('click', '#contact-us', function(e){
        e.preventDefault();
        //$(this).parent().addClass('toggled');
        var token = localStorage.getItem('oaufacewars-token');
        var name = $('body').find('#contact-name').val();
        var email = $('body').find('#contact-email').val();
        var message = $('body').find('#contact-message').val();
        if (name !== '' && email !== '' && message !== '') {
            contact(token,name,email,message);

        } else {
            notify('Fields not set', 'danger', 'center');
        }
    });

});