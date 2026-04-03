
                            
    @if ($revision == 1)

    <input type="text" name="serie{{ $id }}" id='serie{{ $id }}' style="text-transform:uppercase;" placeholder="Revisado" disabled >

    @elseif(!is_null($review_observations))
    <input type="text" name="serie{{ $id }}" id='serie{{ $id }}' style="text-transform:uppercase;" placeholder="{{ $review_observations }}">

    @else  
    <input type="text" name="serie{{ $id }}" id='serie{{ $id }}' style="text-transform:uppercase;"> 

    @endif    

    <input type="checkbox" id="{{ $id }}" name="chkbox{{ $id }}" onclick="revisado({{ $id }})" {{ $revision == 1 ? 'checked' : '' }} {{ $revision == 1 ? 'disabled' : '' }}>

    <div class="modal fade" id="myModal{{ $id }}" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
            <form action="{{ route('admin.revision.observation') }}" method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Observaciones</h4>
        </div>
        <div class="modal-body">
            <p>Las numeros de serie no coinsiden, favor de escribir sus observaciones</p>
                @csrf
                <textarea name="observations" id="observations" class="form-control" rows="3"></textarea>
                <input type="hidden" name="id" id="id" value="{{ $id }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input  type="submit" class="btn btn-primary" value="Enviar">
        </div>
    </div>

    </div>
</div>
            </form>
      