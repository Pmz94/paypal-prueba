$(function() {
    var producto = $('[name="product"]');
    var precio = $('[name="price"]');
    var cantidad = $('[name="quantity"]');
    var subtotal = $('[name="subtotal"]')
    var calculo = 0;
    var total = $('[name="total"]');

    cantidad.keyup(function() {
        calculo = isNaN(parseInt(precio.val() * cantidad.val())) ? 0 : (precio.val() * cantidad.val());
        if(calculo === 0) {
            calculo = 0;
        }
        subtotal.text(calculo);
        total.text(calculo + 1);
    });

    precio.keyup(function() {
        calculo = isNaN(parseInt(precio.val() * cantidad.val())) ? 0 : (precio.val() * cantidad.val());
        if(calculo === 0) {
            calculo = 0;
        }
        subtotal.text(calculo);
        total.text(calculo + 1);
    });
});