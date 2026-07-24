# CraftRootsMP (módulo WooCommerce)

Código del módulo **craftrootsmp** desplegado en el child theme de La Foreste.

> Para archivos nuevos del cliente, usar la carpeta [`../laforeste/`](../laforeste/).

## Despliegue

Subir esta carpeta completa a:

```
wp-content/themes/hello-elementor/craftrootsmp/
```

Y los wrappers de correo en:

```
wp-content/themes/hello-elementor/woocommerce/emails/
```

(desde `../woocommerce/emails/` en la raíz del repo)

## Loader en `functions.php`

```php
require get_stylesheet_directory() . '/craftrootsmp/craftrootsmp-loader.php';
```
