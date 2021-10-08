$(document).ready(function(){
  $(".select2").select2();

  $('.form-check-input').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    radioClass: 'iradio_square-blue',
    increaseArea: '20%' /* optional */
  });

  $(".menu").click(function(e){
    if(!$(e.target).closest('.menu_content').length)
    {
      closeMenu();
    }
  });

  $(".close_menu").click(function(){
    closeMenu();
  });

  $(".menu_icon").click(function(){
    openMenu();
  });
});

function closeMenu()
{
  $(".menu").fadeOut();
  $(".menu_content").removeClass("show");
}

function openMenu()
{
  $(".menu").fadeIn();
  $(".menu_content").addClass("show");
}

function loggedOutAlert()
{
  Swal.fire({
    title: 'Your account was logged out, please login again.',
    icon: 'error',
    confirmButtonText: 'OK',
  }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {
      location.reload();
    }
  });
}

function showError(message, refresh)
{
  if(refresh == 1)
  {
    Swal.fire({
      title: message,
      icon: 'error',
      confirmButtonText: 'OK',
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        location.reload();
      }
    });
  }
  else
  {
    Swal.fire({
      title: message,
      icon: 'error',
      confirmButtonText: 'OK',
    });
  }
}
