Alixar es un fork de Dolibarr usando Alxarafe.

Alxarafe es un paquete, aún en desarrollo, que entre otras, ofrece las siguientes funcionalidades:
- Identificación de usuarios.
- Conexión con bases de datos PDO.
- Gestión de tablas.
- Ayudas a la depuración y el desarrollo de la aplicación (Log, barra de depuración, etc).
- Gestor de plantillas y skins usando Twig.

Su modularidad, le permite cambiar fácilmente las herramientas que utiliza para proporcionar
dichas funcionalidades.

Puede encontrar Alxarafe en los siguientes repositorios:
https://github.com/alxarafe/alxarafe
https://packagist.org/packages/alxarafe/alxarafe

Para integrarlo en su aplicación necesitará instalar composer y ejecutar el siguiente comando:

composer require alxarafe/alxarafe

Si encuentra formas de mejorar el código, hágalo.
PULL REQUEST welcome!

¿Por qué crear Alixar?
----------------------

Dolibarr es un paquete de gestión que cuenta con una sólida comunidad, pero su código está bastante
desorganizado y presenta algunos inconvenientes arrastrados desde sus versiones anteriores, que en
el software actual, por lo general están bastante superados.

Tras analizarlo de manera superficial, comprobamos que dichos inconvenientes pueden solucionarse de
una forma más o menos organizada y mejorar sustancialmente los costos de mantenimiento de la
aplicación.

Algunos de estos inconvenientes son:
- Muchos puntos de entrada al código.
- Desorganización general.
- Una importante cantidad de código duplicado.
- Excesivo uso de variables globales
- Escaso uso de la programación orientada a objetos.
- Mezcla de controladores, modelos y vistas.

Aún así, la herramienta nos parece fenomenal, y por eso nos interesa solucionarlos.

Encontrará el código actualizado en el siguiente repositorio:
https://github.com/alxarafe/alixar

Recuerde que puede colaborar para hacer un código más eficiente.

Podrá ir siguiendo todas las mejoras del código en nuestro blog:
http://alxarafe.es/

Pull requests are always welcome

(El texto original está en español, pero existe copia en inglés https://alxarafe.com)