# PayPalPrueba
![PayPalLogo](https://complyadvantage.com/wp-content/uploads/2015/04/paypal.png)
Usando uno de los APIs de PayPal para integrar su sistema de pagos.

-----------------------

## API REST usado
![PayPalSDKPHPBanner](https://raw.githubusercontent.com/wiki/paypal/PayPal-PHP-SDK/images/homepage.jpg)
[PayPal SDK para PHP](https://paypal.github.io/PayPal-PHP-SDK/)

## Como funciona
![UML](asset/img/uml.png)

## Instrucciones
Hasta ahora lo que se puede hacer con el programa es:
- Escribir un solo producto, precio unitario y cantidad.

![index](asset/img/index.png)

- Redireccionarte al checkout de PayPal.
- Iniciar sesion con tu cuenta de PayPal.
    > Se supone que PayPal por defecto te deja pagar como invitado sin iniciar sesion, "aveces" en el checkout aparece el boton ese.
- Verificar el carrito que te muestra PayPal y pagar.

![checkout](asset/img/checkout.png)

- Ya que se carga el pago se agrega a una base de datos
    > **Ojo:** la base de datos debe estar ya creada con todo y tabla.
- Mostrar la informacion

![pagorealizado](asset/img/pagorealizado.png)

- Emitir un webhook para notificar cualquier movimiento (tarda un tiempo)
    > Hay que tener configurado el link hacia donde va el webhook