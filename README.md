# Plugin de favoritos y comparador / Favorites & Compare Plugin

[Español](#español) · [English](#english)

---

## Español

Plugin de WordPress para un catálogo de vehículos (post type `vehiculo`) que permite a los visitantes **guardar favoritos** y **comparar vehículos lado a lado**, integrado con el Loop Grid de Elementor.

**Versión:** 1.0.111

### Qué hace

- Botones de **favorito** y **comparar** como widgets de Elementor, listos para usar dentro de un Loop Grid.
- Guarda la selección de cada visitante (con o sin sesión iniciada) en una tabla propia de la base de datos, hasta 8 vehículos por lista.
- Filtra un Loop Grid de Elementor para mostrar solo los favoritos o los vehículos a comparar.
- Tabla comparativa por shortcode, agrupada por secciones de características, con insignias (nuevo ingreso, oferta, híbrido, blindado).
- Actualización por AJAX sin recargar la página y mensajes cuando se llega al límite.

### Requisitos

- WordPress 6.0 o superior
- PHP 7.4 o superior
- Elementor y Elementor Pro (Loop Grid)
- Un post type `vehiculo` con sus campos de características

### Instalación

1. En esta página, abre el botón verde **Code** y elige **Download ZIP**.
2. En WordPress, ve a *Plugins → Añadir nuevo → Subir plugin*, elige el ZIP y pulsa **Instalar ahora**.
3. Activa el plugin. Al activarse crea su tabla en la base de datos.
4. En Elementor, agrega los widgets de favorito y comparar a la plantilla de la tarjeta del vehículo, y usa el shortcode `[cpl_compare]` en la página de comparación.

### Desarrollo

Desarrollado por **Fer Santizo** · [github.com/Fer1605](https://github.com/Fer1605)

### Licencia

GPL-2.0-or-later

---

## English

WordPress plugin for a vehicle catalog (`vehiculo` post type) that lets visitors **save favorites** and **compare vehicles side by side**, integrated with the Elementor Loop Grid.

**Version:** 1.0.111

### Features

- **Favorite** and **compare** buttons as Elementor widgets, ready to use inside a Loop Grid.
- Stores each visitor's selection (logged in or not) in its own database table, up to 8 vehicles per list.
- Filters an Elementor Loop Grid to show only the favorites or the vehicles to compare.
- Comparison table via shortcode, grouped by feature sections, with badges (new arrival, offer, hybrid, armored).
- AJAX updates without reloading the page, and messages when the limit is reached.

The front-end texts are in Spanish.

### Requirements

- WordPress 6.0 or later
- PHP 7.4 or later
- Elementor and Elementor Pro (Loop Grid)
- A `vehiculo` post type with its feature fields

### Installation

1. On this page, open the green **Code** button and choose **Download ZIP**.
2. In WordPress, go to *Plugins → Add New → Upload Plugin*, choose the ZIP and click **Install Now**.
3. Activate the plugin. On activation it creates its database table.
4. In Elementor, add the favorite and compare widgets to the vehicle card template, and use the `[cpl_compare]` shortcode on the comparison page.

### Author

Developed by **Fer Santizo** · [github.com/Fer1605](https://github.com/Fer1605)

### License

GPL-2.0-or-later
