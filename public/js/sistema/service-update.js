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
        else{
          event.preventDefault();
          var $saving = $('#modalGuardando');
          if ($saving.length) {
            $saving.modal('show');
          }
          guradarSeguimiento(event.submitter.name);
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();

function getService(id) {
  $.ajax({
    url: 'get-service',
    type: 'GET',
    dataType: 'JSON',
    data: {'id': id},
  })
  .done(function(datos)
  {
    $('#id').val(id);
    $('#id-service').html(id);
    $('#service').html(datos.failure.name);
    $('#description').html(datos.description);
    $('#fecha_seguimiento').html(datos.fecha_seguimeinto);
    
    $('#div_solucion').hide();
    $('#option_finalizado').hide();
    $('#option_seguimiento').show();
    $('#option_ticket_erroneo').show();
    $('#solution').prop('required', false);
    if(datos.fecha_seguimiento != null){
      $('#option_finalizado').show();
      $('#option_seguimiento').hide();
      $('#option_ticket_erroneo').hide();
      $('#div_solucion').show();
      $('#observations').html(datos.observations);
      $('#solution').prop('required', true);
    }

    $('#modalSeguimiento').modal('show');
  });
}


function getServiceCliente (id) {
  $.ajax({
    url: 'get-service',
    type: 'GET',
    dataType: 'JSON',
    data: {'id': id},
  })
  .done(function(datos)
  {
    var base = $('#form-observation').data('update-base') || (window.helpdeskServicesUrls && window.helpdeskServicesUrls.update);
    if (base) {
      $('#form-observation').attr('action', base + '/' + id);
    }
    $('#id-observations').val(id);
    $('#modal-obs-id-service').text(id);
    $('#modal-obs-service-name').text(datos.failure && datos.failure.name ? datos.failure.name : '');
    $('#modalObservaciones').modal('show');
  });
}

if(window.location.hash === '#update') {
  $("#modalSeguimiento").modal('show');
}

$("#modalSeguimiento").on('shown.bs.modal', function() {
  window.location.hash = '#update';
});

$("#modalSeguimiento").on('hide.bs.modal', function() {
  window.location.hash = '#';
});

$('#modalSeguimiento').on('hidden.bs.modal', function() {
  if ($('.modal:visible').length) {
    $('body').addClass('modal-open');
  }
});

$('#modalSeguimiento').on('hidden.bs.modal', function() {
  if ($('.modal:visible').length) {
    $('body').addClass('modal-open');
  }
});

if(window.location.hash === '#updateObservation') {
  $("#modalObservaciones").modal('show');
}

$("#modalObservaciones").on('shown.bs.modal', function() {
  window.location.hash = '#updateObservation';
});

$("#modalObservaciones").on('hide.bs.modal', function() {
  window.location.hash = '#';
});

$('#modalObservaciones').on('hidden.bs.modal', function() {
  if ($('.modal:visible').length) {
    $('body').addClass('modal-open');
  }
});

$('#modalObservaciones').on('hidden.bs.modal', function() {
  if ($('.modal:visible').length) {
    $('body').addClass('modal-open');
  }
});


function guradarSeguimiento(accion) {
  var form = $('#form-seguimiento')[0];
  var form_action = $('#form-seguimiento').attr('action');
  if(accion == 'btn-observacion') {
    var form = $('#form-observation')[0];
    var form_action = $('#form-observation').attr('action');
  }
  var formData = new FormData(form);
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    dataType: 'json',
    type:'POST',
    url: form_action,
    data:formData,
    cache: false,
    contentType: false,
    processData: false
  }).done(function(response)
  {
    var $saving = $('#modalGuardando');
    if ($saving.length) {
      $saving.modal('hide');
    }
    if (response.mensaje === 'OK')
    { 
      $('#modalSeguimiento').modal('hide');
      $(location).attr('href', 'home');
    }
    else
    {
      alert('error al guardar intentolo nuevamente..');
    }
  })
  .fail((errors) => {
    var $saving = $('#modalGuardando');
    if ($saving.length) {
      $saving.modal('hide');
    }
    $(':input[type="submit"]').prop('disabled', false);
    $('#validation-errors').empty();
    var erroCertificado = 0;
    $.each(errors.responseJSON.errors, function(key,value) {
      $('#validation-errors').append('<div class="alert alert-danger">'+value+'</div');
      if(key == 'certificado')
      erroCertificado ++;
      if(erroCertificado >= 1)
      $('#certificado').addClass('is-invalid');
      else
      $('#certificado').removeClass('is-invalid');
    });
  });
}
