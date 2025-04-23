$(document).ready(function(){
  // Add user
  $(document).on('click', '.user_add', function(){
    //user Info
    var user_id = $('#user_id').val();
    var name = $('#name').val();
    var number = $('#number').val();
    var email = $('#email').val();
    //Additional Info
    var dev_uid = $('#dev_uid').val();
    var gender = $(".gender:checked").val();
    var dev_uid = $('#dev_sel option:selected').val();
    
    $.ajax({
      url: 'manage_users_conf.php',
      type: 'POST',
      data: {
        'Add': 1,
        'user_id': user_id,
        'name': name,
        'number': number,
        'email': email,
        'dev_uid': dev_uid,
        'gender': gender,
      },
      success: function(response){

        if (response == 1) {
          $('#user_id').val('');
          $('#name').val('');
          $('#number').val('');
          $('#email').val('');

          $('#dev_sel').val('0');
          $('.alert_user').fadeIn(500);
          $('.alert_user').html('<p class="alert alert-success">A new User has been successfully added</p>');
        }
        else{
          $('.alert_user').fadeIn(500);
          $('.alert_user').html('<p class="alert alert-danger">'+ response + '</p>');
        }

        setTimeout(function () {
            $('.alert').fadeOut(500);
        }, 5000);
        
        $.ajax({
          url: "manage_users_up.php"
          }).done(function(data) {
          $('#manage_users').html(data);
        });
      }
    });
  });
  // Update user
  $(document).on('click', '.user_upd', function(){
    //user Info
    var user_id = $('#user_id').val();
    var name = $('#name').val();
    var number = $('#number').val();
    var email = $('#email').val();
    //Additional Info
    var dev_uid = $('#dev_uid').val();
    var gender = $(".gender:checked").val();
    var dev_uid = $('#dev_sel option:selected').val();

    $.ajax({
      url: 'manage_users_conf.php',
      type: 'POST',
      data: {
        'Update': 1,
        'user_id': user_id,
        'name': name,
        'number': number,
        'email': email,
        'dev_uid': dev_uid,
        'gender': gender,
      },
      success: function(response){

        if (response == 1) {
          $('#user_id').val('');
          $('#name').val('');
          $('#number').val('');
          $('#email').val('');

          $('#dev_sel').val('0');
          $('.alert_user').fadeIn(500);
          $('.alert_user').html('<p class="alert alert-success">The selected User has been updated!</p>');
        }
        else{
          $('.alert_user').fadeIn(500);
          $('.alert_user').html('<p class="alert alert-danger">'+ response + '</p>');
        }
        
        setTimeout(function () {
            $('.alert').fadeOut(500);
        }, 5000);
        
        $.ajax({
          url: "manage_users_up.php"
          }).done(function(data) {
          $('#manage_users').html(data);
        });
      }
    });   
  });
  // delete user
  $(document).on('click', '.user_rmo', function(){

    var user_id = $('#user_id').val();

    bootbox.confirm("Do you really want to delete this User?", function(result) {
      if(result){
        $.ajax({
          url: 'manage_users_conf.php',
          type: 'POST',
          data: {
            'delete': 1,
            'user_id': user_id,
          },
          success: function(response){

            if (response == 1) {
              $('#user_id').val('');
              $('#name').val('');
              $('#number').val('');
              $('#email').val('');

              $('#dev_sel').val('0');
              $('.alert_user').fadeIn(500);
              $('.alert_user').html('<p class="alert alert-success">The selected User has been deleted!</p>');
            }
            else{
              $('.alert_user').fadeIn(500);
              $('.alert_user').html('<p class="alert alert-danger">'+ response + '</p>');
            }
            
            setTimeout(function () {
                $('.alert').fadeOut(500);
            }, 5000);
            
            $.ajax({
              url: "manage_users_up.php"
              }).done(function(data) {
              $('#manage_users').html(data);
            });
          }
        });
      }
    });
  });
  // select user
  $(document).on('click', '.select_btn', function(){
    var el = this;
    var card_uid = $(this).attr('id');
    
    // Remove highlight from all rows
    $('tr').css('background', '');
    
    // First select the card
    $.ajax({
      url: 'manage_users_conf.php',
      type: 'POST',
      data: {
        'select_card': 1,
        'card_uid': card_uid
      },
      success: function(response){
        if (response == 1) {
          // Then get the user data
          $.ajax({
            url: 'manage_users_conf.php',
            type: 'POST',
            data: {
              'get_user': 1,
              'card_uid': card_uid
            },
            success: function(userData){
              if (userData) {
                var data = JSON.parse(userData);
                $('#user_id').val(data.id);
                $('#name').val(data.username);
                $('#number').val(data.serialnumber);
                $('#email').val(data.email);
                $('#dev_sel').val(data.device_uid);
                
                if (data.gender == 'Female'){
                  $('.form-style-5').find(':radio[name=gender][value="Female"]').prop('checked', true);
                } else {
                  $('.form-style-5').find(':radio[name=gender][value="Male"]').prop('checked', true);
                }
                
                // Show success message
                $('.alert_user').fadeIn(500);
                $('.alert_user').html('<p class="alert alert-success">Card selected successfully!</p>');
                
                // Refresh the table to show updated checkmark
                $.ajax({
                  url: "manage_users_up.php"
                }).done(function(data) {
                  $('#manage_users').html(data);
                  // Highlight the selected row after refresh
                  $('.select_btn[id="' + card_uid + '"]').closest('tr').css('background','#70c276');
                });
              }
            }
          });
        } else {
          $('.alert_user').fadeIn(500);
          $('.alert_user').html('<p class="alert alert-danger">' + response + '</p>');
        }
        
        setTimeout(function () {
          $('.alert').fadeOut(500);
        }, 5000);
      }
    });
  });
});