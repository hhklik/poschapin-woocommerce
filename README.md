
<p align="center"><a href="https://poschapin.com/"><img src="https://user-images.githubusercontent.com/37667605/86314652-713dc580-bbe5-11ea-83eb-749a41f60c65.png" alt="WooCommerce"></a></p>



# poschapin-woocommerce
~Current Version:1.4~

Plugin WordPress WooCommerce para recolección de pagos con tarjetas de credito y debito

### Pre-requisitos

WooCommerce instalado.

### Instalar plugin en WordPress
- Descargar el zip del repositorio github, se descargara un archivo zip llamado **poschapin-woocommerce-master.zip** 
  - [Click aqui para descargar](https://github.com/hhklik/poschapin-woocommerce/archive/master.zip)
- Dentro del panel de control de **wordpress(su tienda en linea)** hacer lo siguiente  `plugins > añadir nuevo > click boton subir plugin > click en booton seleccionar plugin` **poschapin-woocommerce-master.zip**
- Activar plugin

### Configurar public_key y private_key
- Solicitar creación de partner(Socio de negocios) en **admin@poschapin.com** Si ya es partner(Socio de negocios) salte este paso
- Con acceso a panel de control de POSchapin, hacer lo siguiente `wallet > Elegir wallet y dar click en EDIT` y en esta parte al entrar se visualizara el **public_key y private_key** para recolectar pagos para dicha billetera.
  - Si no logras procesar tu pago, en panel de control de POSchapin en `wallet > Elegir wallet y dar click en EDIT` vas a encontrar el siguiente icono <img src="https://poschapin.com/wp-content/assets/img/key_reload.svg" width="25" alt="reload_keys"> dando click en este icono y luego actualizar se podra crear otra **private_key** o **public_key**
- En el panel de control de **wordpress(su tienda en linea)** ingrese a `WooCommerce > settings > click en el tab o ficha payments > click en boton manager` 
- Copiar y pegar **Public_key**
- Copiar y pegar **Private_key**
- Click en boton **Save Changes**

Y listo ya puede comenzar a recolectar pagos.

### Cambiar llaves publicas y privada de una billetera
Ingresando al panel de control de POSchapin dirijase a `wallet > Elegir wallet y dar click en EDIT` en ese apartado se va visualizar el siguiente icono 
- <img src="https://poschapin.com/wp-content/assets/img/key_reload.svg" width="25" alt="reload_keys"> al dar click aparecera el siguiente icono y con esto estas solicitando cambio de llave de seguridad ya sea **public_key** o **private_key**  
- <img src="https://poschapin.com/wp-content/assets/img/return.svg" width="25" alt="reload_keys"> con el cual puedes retornar a la lleve de seguridad anterior
- Luego click **Actualizar** o **update**

### Support

- Puedes enviar un correo a `support@poschapin.com`


## Autores

- Humberto Herrador (para POSchapin 2020)






