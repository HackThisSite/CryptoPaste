// Set elapsed time
function setElapsedTime() {
  // Set number values
  var seconds = Date.now() / 1000 - timestamp;
  var minutes = seconds / 60;
  seconds %= 60;
  var hours = minutes / 60;
  minutes %= 60;
  var days = hours / 24;
  hours %= 24;
  var weeks = days / 7;
  days %= 7;
  var years = weeks / 52;
  weeks %= 52;
  // Create string
  var elapsedTime = "";
  if (years >= 1) {
    elapsedTime += Math.floor(years) + "y ";
  }
  if (weeks >= 1 || elapsedTime) {
    elapsedTime += Math.floor(weeks) + "w ";
  }
  if (days >= 1 || elapsedTime) {
    elapsedTime += Math.floor(days) + "d ";
  }
  if (hours >= 1 || elapsedTime) {
    elapsedTime += Math.floor(hours) + "h ";
  }
  if (minutes >= 1 || elapsedTime) {
    elapsedTime += Math.floor(minutes) + "m ";
  }
  if (seconds >= 1 || elapsedTime) {
    elapsedTime += Math.floor(seconds) + "s";
  }
  // Set string to element
  var timestr = new Date(timestamp * 1000).toLocaleString();
  $("#timestamp").html(timestr+' (<em>'+elapsedTime+' ago</em>)');
}

// Show error alert
function showError(message) {
  $("#errmsg").text(message);
  $("#erralert").slideDown('fast');
}

// Enable/disable decrypt button
function enableDecrypt() {
  if ($("#password").val()) {
    tooltip = 'Click here to decrypt this paste.';
    $("#btn-decrypt")
      .removeClass('disabled')
      .attr('data-original-title', tooltip)
      .tooltip('fixTitle');
  } else {
    tooltip = 'You must enter the password to decrypt this paste.';
    $("#btn-decrypt")
      .addClass('disabled')
      .attr('data-original-title', tooltip)
      .tooltip('fixTitle');
  }
}

// Define jQuery stuff
$(document).ready(function() {
  // Start the Stanford JavaScript Crypto Library (SJCL) entropy collectors
  sjcl.random.startCollectors();

  // Ensure things are properly en/disabled
  $("#hjslist").attr('disabled', true);
  enableDecrypt();

  // Enable tooltips
  $('[data-toggle="tooltip"]').tooltip();

  // Populate syntax highlighting languages
  var langs = hljs.listLanguages().sort();
  langs.forEach(function(lang) {
    $("#hjslist").append('<option value="'+lang+'">'+lang+'</option>');
  });

  // Set syntax highlighting language
  $("#hjslist").change(function() {
    if ($(this).val() == '') {
      $("#paste")
        .removeClass()
        .addClass('hljs');
    } else {
      $("#paste")
        .removeClass()
        .addClass('hljs lang-'+$(this).val());
    }
    hljs.highlightBlock($("#paste").get(0));
    window.scroll({ top: 0, left: 0, behavior: 'smooth' });
  });

  // Update and start timestamp counter
  setElapsedTime();
  setInterval(function() {
    setElapsedTime();
  }, 1000);

  // Password field key press
  $("#password").keypress(function(event) {
    if (event.which == 13)
      event.preventDefault();
  });
  $("#password").keyup(function(event) {
    enableDecrypt();
  });

  // Decrypt paste
  $("#btn-decrypt").click(function() {
    // Halt if the button is disabled
    if ($(this).hasClass('disabled')) return false;

    // Hide error window if shown
    $("#erralert").slideUp('fast');

    // Attempt to decrypt paste
    try {
      // Decrypt, escape, and set paste
      var decrypted = sjcl.decrypt($("#password").val(), sjcl.codec.utf8String.fromBits(sjcl.codec.base64.toBits($("#paste").html())), {ks: 256});
      cleaned_paste = decrypted.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      $("#paste").html(cleaned_paste);

      // Update Highlight.js list
      $("#hjslist")
        .attr('disabled', false)
        .attr('data-original-title', 'Select the language of this paste to apply syntax highlighting.')
        .tooltip('fixTitle');

      // Disable button and password fields
      $("#btn-decrypt")
        .addClass('disabled')
        .attr('data-original-title', 'This paste has been decrypted.')
        .tooltip('fixTitle');
      $("#password")
        .attr('disabled', true)
        .attr('placeholder', '[Password Flushed]')
        .val(null);

      // Scroll to top
      window.scroll({ top: 0, left: 0, behavior: 'smooth' });
    } catch (e) {
      showError(e);
    }
  });

  // Close error alert
  $("#closeerr").click(function() {
    $("#erralert").slideUp('fast');
  });

  // Hide loading screen and show paste once everything is loaded
  $("#loading").slideUp('fast', function() {
    $("#view-paste").slideDown('fast', function() {
      window.scroll({ top: 0, left: 0, behavior: 'smooth' });
//      hljs.initHighlighting();
    });
  });

});

// Show a friendly message
console.log("Guten tag! Feel free to inspect CryptoPaste's source code for proof of security! https://github.com/HackThisCode/CryptoPaste");
