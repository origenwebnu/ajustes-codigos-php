# La Foreste (laforeste.com)

Carpeta del cliente **La Foreste** para archivos nuevos y desarrollos futuros.

## Importante: separación de carpetas

| Carpeta en el repo | Qué es |
|--------------------|--------|
| **`laforeste/`** (esta carpeta) | Archivos nuevos que subas o desarrollemos para La Foreste |
| **`craftrootsmp/`** (raíz del repo) | Módulo WooCommerce ya existente en el servidor (carrito, checkout, correos, Stripe) |

No mezclar: lo nuevo va aquí en `laforeste/`. El código desplegado en el tema sigue en `craftrootsmp/`.

## Rama de trabajo

`cursor/laforeste-230f`

## Despliegue del módulo existente (`craftrootsmp/`)

Eso sigue yendo al child theme en el servidor:

- `craftrootsmp/` → `wp-content/themes/hello-elementor/craftrootsmp/`
- `woocommerce/emails/` → `wp-content/themes/hello-elementor/woocommerce/emails/`

Los archivos que agregues en `laforeste/` los definiremos según vayan llegando (tema, plugins, assets, etc.).
