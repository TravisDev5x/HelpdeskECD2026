(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();

document.addEventListener('DOMContentLoaded', function () {
  const categoriaSelect = document.getElementById('ctg_contenido_id');
  const subcategoriaSelect = document.getElementById('ctg_subcategoria_id');

  categoriaSelect.addEventListener('change', function () {
      const categoriaId = this.value;

      // Limpia las subcategorías anteriores
      subcategoriaSelect.innerHTML = '<option value="" disabled selected>Cargando...</option>';
      subcategoriaSelect.disabled = true;

      fetch(`/admin/subcategorias/${categoriaId}`)
      .then(response => response.json())
          .then(data => {
              subcategoriaSelect.innerHTML = '<option value="" disabled selected>Selecciona...</option>';
              data.forEach(subcategoria => {
                  const option = document.createElement('option');
                  option.value = subcategoria.id;
                  option.text = subcategoria.subcategoria; // Asegúrate de que sea el nombre correcto del campo
                  subcategoriaSelect.appendChild(option);
              });
              subcategoriaSelect.disabled = false;
          })
          .catch(error => {
              console.error('Error al cargar subcategorías:', error);
              subcategoriaSelect.innerHTML = '<option value="" disabled selected>Error al cargar</option>';
          });
  });
});
