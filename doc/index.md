# Aropixel Page Bundle

<div align="center">
    <img width="100" height="100" src="assets/logo-aro.png" alt="aropixel logo" />
</div>

## Presentation

Aropixel Page Bundle is a module for Symfony that allows you to manage pages in your application. 
It integrates seamlessly with the Aropixel Admin Bundle to provide a clean management interface.

Currently, it supports:
- **Default Pages**: Standard pages where content is stored as HTML.
- **Custom JSON Pages**: Powerful page types with structured forms, storing data as JSON.
- **Fixed Pages**: Define non-deletable system pages with unique identifiers.

## Key Features

* **Easy Installation**: Minimal configuration required to get started.
* **Translatable Content**: Full support for multi-language pages using Gedmo Translatable.
* **SEO Management**: Built-in fields for Meta Title, Meta Description, and Meta Keywords.
* **Fixed Pages**: Define system pages that are protected and non-deletable.
* **Custom JSON Types**: Map Symfony form fields directly to JSON storage.
* **Flexible Data Model**: Uses interfaces for entities, allowing you to easily extend the `Page` and `PageTranslation` models.
* **Status & Scheduling**: Manage publication status and schedule when pages should be visible.

## Further documentation

Discover more by reading the docs:

* [Getting started with AropixelPageBundle](installation.md)
* [Entity Customization](entities.md)
* [Usage and Page Types](usage.md)
