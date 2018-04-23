$(function() {
    //index
    function calcular() {
        var precio = $('[name="price"]');
        var cantidad = $('[name="quantity"]');
        var subtotal = isNaN(parseFloat(precio.val() * cantidad.val())) ? 0 : (precio.val() * cantidad.val());
        var total = subtotal + 1;
        if(precio === 0 && cantidad === 0 || subtotal === 0) {
            subtotal = 0;
            total = 0;
        }
        $('#subtotal').text(subtotal);
        $('#total').text(total);
    }

    $('[name="price"]').keyup(function() {
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
        order: [],
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
            infoFiltered: '(filtrado de los _MAX_ renglones)',
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

    $(document).on('click', '.view', function () {
        var idTransaccion = $(this).attr('id');
        $.ajax({
            url: 'vercadapago.php',
            method: 'POST',
            data: { idTransaccion: idTransaccion },
            dataType: 'json',
            success: function (data) {
                $('#pagosModal').modal('show');

                /*$('#model').val(data.model);
                $('#speed').val(data.speed);
                $('#ram').val(data.memory);
                $('#hdd').val(data.hdd);
                $('#price').val(data.price);*/

                $('.modal-title').text('Detalles de pago');
            }
        })
    });

});