$(function() {
    var precio = $('[name="price"]');
    var cantidad = $('[name="quantity"]');

    function calcular() {
        var subtotal = isNaN(parseInt(precio.val() * cantidad.val())) ? 0 : (precio.val() * cantidad.val());
        var total = subtotal + 1;
        if(precio === 0 && cantidad === 0 || subtotal === 0) {
            subtotal = 0;
            total = 0;
        }
        $('#subtotal').text(subtotal);
        $('#total').text(total);
    }

    cantidad.keyup(function() {
        calcular();

    });

    precio.keyup(function() {
        calcular();
    });
});