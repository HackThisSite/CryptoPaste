// Refresh CAPTCHA
function refreshCaptcha() {
  document.getElementById('captcha_image').src = captcha_img_url+'?'+Math.random();
  captcha_image_audioObj.refresh();
  $("#captcha_code").val(null);
}

// Set form to read-only (disabled) or enabled
function setFormReadonly(readonly) {
  $('#new-paste').find('input, textarea, button, select').attr('disabled',(readonly ? 'disabled' : false));
}

// Show error alert
function showError(message) {
  $("#errmsg").text(message);
  $("#erralert").slideDown('fast');
}

// Enable/disable submit button
function enableSubmit() {
  if ($("#password").val() && $("#paste").val() && $("#captcha_code").val()) {
    tooltip = 'Click here to encrypt and submit your paste!';
    $("#btn-submit")
      .removeClass('disabled')
      .attr('data-original-title', tooltip)
      .tooltip('fixTitle');
  } else {
    tooltip = 'You must fill out the fields above before you can encrypt and submit your paste.';
    $("#btn-submit")
      .addClass('disabled')
      .attr('data-original-title', tooltip)
      .tooltip('fixTitle');
  }
}

// Show password strength using the Dropbox zxcvbn library
function showStrength(result) {
  if (result.password.length <= 0) {
    $("#passwd_progress")
      .width(5)
      .removeClass('progress-bar-success progress-bar-info progress-bar-warning progress-bar-danger')
      .addClass('progress-bar-primary')
      .text('');
    $("#passwd_helper").text('Set or generate a password');
    return;
  }
  var percentage = 0;
  var css = 'danger';
  var text = '';
  switch (result.score) {
    case 0:
      percentage = 5;
      css = 'danger';
      text = "Bad";
      break;
    case 1:
      percentage = 25;
      css = 'danger';
      text = "Very Weak";
      break;
    case 2:
      percentage = 50;
      css = 'warning';
      text = "Weak";
      break;
    case 3:
      percentage = 75;
      css = 'info';
      text = "Medium";
      break;
    case 4:
      percentage = 100;
      css = 'success';
      var crackTime = String(result.crack_times_display.offline_fast_hashing_1e10_per_second);
      if (crackTime.indexOf("years") !=-1) {
        text = "Very Strong";
      } else if (crackTime.indexOf("centuries") !=-1) {
        text = "Perfect";
      } else {
        text = "Strong";
      }
      break;
  }
  $("#passwd_progress")
    .width(percentage+'%')
    .removeClass('progress-bar-success progress-bar-info progress-bar-warning progress-bar-danger')
    .addClass('progress-bar-'+css)
    .text(text);
  var helper = result.feedback.suggestions.slice();
  if (result.feedback.warning) helper.unshift(result.feedback.warning);
  $("#passwd_helper").html(helper.join(' &ndash; '));
}

// Define jQuery stuff
$(document).ready(function() {
  // Flush form in case of page reload
  $('#new-paste').trigger("reset");
  showStrength(zxcvbn($("#password").val()));
  enableSubmit();

  // Start the Stanford JavaScript Crypto Library (SJCL) entropy collectors
  sjcl.random.startCollectors();

  // Enable tooltips
  $('[data-toggle="tooltip"]').tooltip();

  // Enable clipboard button
  var clipboard = new Clipboard('#clipboard');

  // Generate random password
  $("#genpasswd").click(function(){
    $("#password").attr('type', 'text');
    passwd = $('#password');
    // Check that enough entropy has been collected
    if (sjcl.random.isReady()) {
      try {
        // Populate the password with 12 random words encoded as a Base64 string equivalent to 64 characters
        $(password).val(sjcl.codec.base64.fromBits(sjcl.random.randomWords(12)));
        $(password).select();
        showStrength(zxcvbn($("#password").val()));
      } catch (e) {
        showError(e)
      }
    } else {
      showError("Random number generator requires more entropy, please wait a moment.");
    }
  });

  // Password field key press
  $("#password").keypress(function(event) {
    if (!event.ctrlKey && $("#password").attr('type') == 'text') {
      $("#password").attr('type', 'password');
    }
    if (event.which == 13)
      event.preventDefault();
  });
  $("#password").keyup(function(event) {
    showStrength(zxcvbn($("#password").val()));
  });
  $("#password").change(function(event) {
    showStrength(zxcvbn($("#password").val()));
  });

  // New paste input field key-up
  $(".npinput").change(function() {
    enableSubmit();
  });
  $(".npinput").keyup(function() {
    enableSubmit();
  });

  // CAPTCHA refresh button click
  $("#captcha_refresh").click(function() {
    refreshCaptcha();
  });

  // Encrypt and submit paste
  $("#btn-submit").click(function() {
    // Halt if the button is disabled
    if ($(this).hasClass('disabled')) return false;

    // Keep a temporary copy of the plain text to restore if unsuccessful
    var original = $("#paste").val();

    // Try to encrypt and send
    try {
      // Disable form elements to prevent alteration
      setFormReadonly(true);

      // Encrypt the plain text using the password to a Base64 string
      var enc_paste = sjcl.codec.base64.fromBits(sjcl.codec.utf8String.toBits(sjcl.encrypt($("#password").val(), original, {ks: 256})));
      $("#paste").val(enc_paste);

      // Check that the paste text is below the maximum character limit
      if ($("#paste").val().length < window.maxlength) {
        // Clear the password and plain text as a security measure
        $("#password").val(null);

        // Submit paste data
        $.ajax({
          url: process_url,
          method: 'POST',
          dataType: 'json',
          data: {
            'nonce': $("#nonce").val(),
            'paste': enc_paste,
            'expiry': $("#expiration").val(),
            'captcha': $("#captcha_code").val(),
          },
          success: function(data) {
            original = null;

            // Hide form and show submitted dialog
            $("#new-paste").slideUp('fast', function() {
              // Slide the window up
              try {
                window.scroll({ top: 0, left: 0, behavior: 'smooth' });
              } catch (e) {
                window.scrollTo(0,0);
              }

              // Set paste URL
              $("#paste-url").val(data.url);

              // Show expiration
              $("#submitted-expiry").text(data.expires == 'burn' ? 'This paste will be deleted once it is opened.' : 'This paste will expire: '+data.expires);

              // Show burn notice (hehe)
              if ($("#expiration").val() == 'once') $("#burn-notice").show();

              // Select URL field when focused
              $("#paste-url").focus(function() {
                $(this).select();
              });

              // Show submitted dialog
              $("#submitted").slideDown('fast', function() {
                $("#paste-url").select();
              });
            });
          },
          error: function(jqXHR, errtext) {
            // Set JSON object
            try {
              var json = jQuery.parseJSON(jqXHR.responseText);
            } catch (e) {
              var json = {'message': 'A server error occurred. Please try again or inform the owner of this CryptoPaste.'};
            }

            // Restore the original plain text from the temporary copy
            $("#paste").val(original);
            original = null;

            // Reset input element states
            showStrength(zxcvbn($("#password").val()));
            enableSubmit();
            refreshCaptcha();

            // Change nonce if provided
            if (json.nonce) $("#nonce").val(json.nonce);

            // Reset the form elements as editable
            setFormReadonly(false);

            // Show error
            showError(json.message);

            // Slide the window up
            try {
              window.scroll({ top: 0, left: 0, behavior: 'smooth' });
            } catch (e) {
              window.scrollTo(0,0);
            }
          }
        });

      } else {
        // Restore the original plain text from the temporary copy
        $("#paste").val(original);
        original = null;

        // Reset the form elements as editable
        setFormReadonly(false);

        // Show error
        showError("Maximum length exceeded. Please reduce the size of your paste.");
      }
    } catch (e) {
      showError(e);
    }
  });

  // Clear form
  $("#btn-clear").click(function() {
    $("#confirm-reset").modal('show');
  });
  $("#confirm-reset-btn").click(function() {
    $('#new-paste').trigger("reset");
    showStrength(zxcvbn($("#password").val()));
    enableSubmit();
    $("#confirm-reset").modal('hide');
  });

  // Close error alert
  $("#closeerr").click(function() {
    $("#erralert").slideUp('fast');
  });

  // Hide loading screen and show form once everything is loaded
  $("#loading").slideUp('fast', function() {
    $("#new-paste").slideDown('fast', function() {
      try {
        window.scroll({ top: 0, left: 0, behavior: 'smooth' });
      } catch (e) {
        window.scrollTo(0,0);
      }
      $("#paste").focus();
    });
  });

});

// Show a friendly message
console.log("Guten tag! Feel free to inspect CryptoPaste's source code for proof of security! https://github.com/HackThisCode/CryptoPaste");
