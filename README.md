
<p align="center"><a href="https://poschapin.com/"><img src="https://user-images.githubusercontent.com/37667605/86309706-9af0ef80-bbd9-11ea-84e9-126d7c5aa547.png" alt="WooCommerce"></a></p>



# poschapin-woocommerce

Plugin WordPress WooCommerce para recolección de pagos con tarjetas de credito y debito

### Pre-requisitos

WooCommerce instalado.

### Instalar plugin en WordPress
- Descargar el zip del repositorio github, se descargara una carpeta llamada **poschapin-woocommerce-master** 
  - [Click aqui para descargar](https://github.com/hhklik/poschapin-woocommerce/archive/master.zip)
- Dentro del panel de control de **wordpress(su tienda en linea)** hacer lo siguiente  `plugins > añadir nuevo > click boton subir plugin > click en booton seleccionar plugin` 
- Activar plugin

### Configurar public_key y private_key
- Solicitar creación de partner(Socio de negocios) en **admin@poschapin.com** Si ya es partner(Socio de negocios) salte este paso
- Con acceso a panel de control de POSchapin, hacer lo siguiente `wallet > Elegir wallet y dar click en EDIT` y en esta parte al entrar se visualizara el **public_key y private_key** para recolectar pagos para dicha billetera.
- En el panel de control de **wordpress(su tienda en linea)** ingrese a `WooCommerce > settings > click en el tab o ficha payments > click en boton manager` 
- Copiar y pegar **Public_key**
- Copiar y pegar **Private_key**
- Click en boton **Save Changes**

Y listo ya puede comenzar a recolectar pagos.

### Support

- Puedes enviar un correo a `support@poschapin.com`


## Autores

- Humberto Herrador (para POSchapin 2020)




