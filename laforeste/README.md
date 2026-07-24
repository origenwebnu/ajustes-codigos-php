# La Foreste (laforeste.com)

Código personalizado para el sitio WooCommerce de **La Foreste**.

## Estructura

```
laforeste/
├── craftrootsmp/          → subir a wp-content/themes/hello-elementor/craftrootsmp/
└── woocommerce/emails/    → subir a wp-content/themes/hello-elementor/woocommerce/emails/
```

## Despliegue en el servidor

1. Subir `laforeste/craftrootsmp/` a:
   `wp-content/themes/hello-elementor/craftrootsmp/`
2. Subir `laforeste/woocommerce/` a:
   `wp-content/themes/hello-elementor/woocommerce/`
3. Verificar en `functions.php` del child theme:
   ```php
   require get_stylesheet_directory() . '/craftrootsmp/craftrootsmp-loader.php';
   ```
4. Limpiar caché LiteSpeed y, si aplica, la caché de plantillas de WooCommerce.

## Módulos

| Carpeta / archivo | Función |
|-------------------|---------|
| `cart.css`, `cart.js` | Carrito (`/carrito/`) |
| `checkout.css`, `checkout.js` | Checkout y pedido recibido |
| `craftrootsmp-email-hooks.php` | Correos al cliente |
| `stripe-express-checkout.*` | Botones Stripe en resumen |

## Rama de trabajo

Usar la rama `cursor/laforeste-230f` para todos los cambios de este cliente.
