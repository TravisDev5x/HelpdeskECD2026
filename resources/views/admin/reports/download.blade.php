
<table>
    <thead>
    <tr>
        <th>NOMBRE</th>
        <th>OPERABLE</th>
        <th>INOPERABLE</th>
        <th>CONSUMIBLE</th>
        <th>ROBADO</th>
        <th>EN REPARACION</th>
        <th>STOCK</th>
        <th>TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($products as $product)
    <tr>
      <td>{{ $product->name }}</td>
      <td class="text-center">{{ $product->OPERABLE }}</td>
      <td class="text-center">{{ $product->INOPERABLE }}</td>
      <td class="text-center">{{ $product->CONSUMIBLE }}</td>
      <td class="text-center">{{ $product->ROBADO }}</td>
      <td class="text-center">{{ $product->RECICLADO }}</td>
      <td class="text-center">{{ $product->EN_REPARACION }}</td>
      <td class="text-center">{{ $product->STOCK }}</td>
      <td class="text-center">{{ $product->cantidad }}</td>
    </tr>
    @endforeach
    </tbody>
</table>

