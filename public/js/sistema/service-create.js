(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        $(':input[type="submit"]').prop('disabled', true);
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
          $(':input[type="submit"]').prop('disabled', false);
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();

$('#area_id').on('change', function() {
  $.ajax({
    url: '../get-failures',
    type: 'GET',
    dataType: 'JSON',
    data: {'area_id': $('#area_id').val()},
  })
  .done(function(datos)
  {
    $('#failure_id').empty();
    $('#failure_id').append('<option value="" disabled selected>Seleccione una tipo de solicitud...</option>');
    $.each(datos, function (index, value){
      $('#failure_id').append('<option value="'+value.id+'">'+value.name+'</option>');
    });
  });
});
