$(function() {
    //index
    function calcular() {
        var price = $('#product option:selected').data('price');
        $('[name="price"]').val(price);
        var quantity = $('[name="quantity"]').val();
        var total = price * quantity;
        if(isNaN(total) === true) {
            $('#total').text(0);
        } else {
            $('#total').text(total);
        }
    }

    $('#product').change(function() {
        calcular();
    });

    $('[name="quantity"]').keyup(function() {
        calcular();
    });

    //pagos
    var dataTable = $('#tablaPagos').DataTable({
        searching: false,
        processing: true,
        serverSide: true,
        ordering: false,
        pagingType: "full",
        lengthMenu: [[10, 15, 20], [10, 15, 20]],
        ajax: {
            url: 'obtenerpagos.php',
            type: 'POST'
        },
        columnDefs: [
            {
                targets: [0, 2, 4, 5],
                orderable: false

            }
        ],
        language: {
            info: 'Pag. _PAGE_ de _PAGES_',
            infoEmpty: 'No hay datos',
            infoFiltered: '(de los _MAX_ renglones)',
            lengthMenu: 'Mostrar _MENU_ renglones por pagina',
            loadingRecords: 'Cargando datos...',
            processing: 'Procesando...',
            search: 'Buscar:',
            zeroRecords: 'No se encontro nada',
            paginate: {
                first: '&#171;',
                last: '&#187;',
                next: '&#8250;',
                previous: '&#8249;'
            }
        }
    });

    $(document).on('click', '.view', function() {
        var idTransaccion = $(this).attr('id');
        $.ajax({
            url: 'vercadapago.php',
            method: 'POST',
            data: {idTransaccion: idTransaccion},
            dataType: 'json',
            success: function(data) {
                $('#pagosModal').modal('show');

                $('#idTransaccion').html('<th>ID de Transaccion:</th><td>' + idTransaccion + '</td>');
                $('#idCarrito').html('<th>ID de Carrito:</th><td>' + data.idCarrito + '</td>');
                $('#correo').html('<th>Comprador:</th><td>' + data.correo + '</td>');
                $('#idVenta').html('<th>ID de Venta:</th><td>' + data.idVenta + '</td>');
                $('#producto').html('<th>Producto:</th><td>' + data.producto + '</td>');
                $('#precio').html('<th>Precio/Unidad:</th><td>$' + data.precio + '</td>');
                $('#cantidad').html('<th>Cantidad:</th><td>' + data.cantidad + '</td>');
                $('#total').html('<th>Total:</th><td>$' + data.total + '</td>');
                $('#fecha').html('<th>Fecha:</th><td>' + data.fecha + '</td>');
                $('#hora').html('<th>Hora:</th><td>' + data.hora + '</td>');

                if(data.estado === 'refunded') {
                    $('#estado').html('<th style="background-color:red;color:white;">Estado:</th><td style="background-color:red;color:white;">' + data.estado + '</td>');
                } else {
                    $('#estado').html('<th>Estado:</th><td>' + data.estado + '</td>');
                }

                $('#payment').text(data.data);
            }
        })
    });

    /*$(document).on('click', '.refund', function() {
        var idVenta = $(this).attr('id');
        if(confirm('Seguro que quieres devolver este producto?')) {
            $.ajax({
                url: 'reembolsarpago.php',
                method: 'POST',
                data: {idVenta: idVenta},
                success: function(data) {
                    alert(data);
                    dataTable.ajax.reload();
                }
            });
        } else {
            return false;
        }
    });*/
});