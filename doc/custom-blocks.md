# Custom Block Types

The page builder supports custom block types defined by the application. This lets you add project-specific content blocks (e.g. an event card, a map embed, a product teaser) without modifying the bundle.

## Overview

Adding a custom block requires two independent steps:

| Step | Where | What |
|---|---|---|
| 1 | `config/packages/aropixel_page.yaml` | Declare the block (type, label, icon, library tab) |
| 2 | Your application JS | Implement the block behaviour |

---

## Step 1 — Declare the block in YAML

```yaml
aropixel_page:
    page_builder:
        custom_blocks:
            - { type: 'my-event', label: 'Événement', icon: 'fas fa-calendar', category: 'custom' }
            - { type: 'my-map',   label: 'Carte',     icon: 'fas fa-map',      category: 'modules' }
```

### Available options

| Key | Required | Default | Description |
|---|---|---|---|
| `type` | yes | — | Unique identifier, used as a key in the JSON content |
| `label` | yes | — | Label shown in the block library |
| `icon` | no | `fas fa-puzzle-piece` | Font Awesome icon class |
| `category` | no | `custom` | Tab where the block appears: `blocs`, `medias`, `modules`, `custom` |

Using `category: custom` adds a dedicated **"Personnalisé"** tab in the library, visible only when at least one block uses it.

---

## Step 2 — Implement the block in JavaScript

Create a JS file in your application (e.g. `assets/page_builder_custom.js`):

```javascript
window.__pageBuilderCustomBlocks = window.__pageBuilderCustomBlocks || [];

window.__pageBuilderCustomBlocks.push({
    type: 'my-event',

    /**
     * Returns the initial data object for a new block.
     */
    create(generateId) {
        return {
            id: generateId(),
            type: 'my-event',
            title: 'Nouvel événement',
            date: '',
        };
    },

    /**
     * Renders the block preview in the canvas.
     * Must return { container } (or { container, contentElement }).
     */
    renderPreview(block, ctx) {
        const container = document.createElement('div');
        container.classList.add('pb-block-content-preview', 'my-event-preview');
        container.innerHTML = `<strong>${block.title || 'Événement'}</strong><br><small>${block.date || ''}</small>`;
        return { container };
    },

    /**
     * Renders the inspector panel controls for this block.
     */
    renderInspector(block, ctx) {
        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');

        if (contentContainer) {
            contentContainer.innerHTML = `
                <div class="mb-2">
                    <label class="form-label form-label-sm">Titre</label>
                    <input type="text" class="form-control form-control-sm"
                           value="${block.title || ''}"
                           data-page-builder-target="blockContentInput"
                           data-action="input->page-builder#updateBlockContent">
                </div>
                <div class="mb-2">
                    <label class="form-label form-label-sm">Date</label>
                    <input type="text" class="form-control form-control-sm"
                           id="my-event-date"
                           value="${block.date || ''}"
                           data-action="input->page-builder#updateBlockContent">
                </div>
            `;
        }

        ctx.blockTitleTarget.textContent = 'Bloc Événement';
        ctx.blockContentInputTarget.value = block.title || '';
    },

    /**
     * Handles inspector input events and updates the block data model.
     */
    handleInspectorInput(block, event) {
        if (event.target.dataset.pageBuilderTarget === 'blockContentInput') {
            block.title = event.target.value;
        }
        if (event.target.id === 'my-event-date') {
            block.date = event.target.value;
            // Refresh preview
            const els = document.querySelectorAll(`.pb-block[data-block-id="${block.id}"] .my-event-preview`);
            els.forEach(el => {
                el.innerHTML = `<strong>${block.title || 'Événement'}</strong><br><small>${block.date || ''}</small>`;
            });
        }
    },
});
```

### Loading the file

This file must be loaded **before** the Stimulus page-builder controller initialises.

**With Symfony AssetMapper**, add it to your `importmap.php`:

```php
// importmap.php
return [
    // ...
    'page-builder-custom' => [
        'path' => './assets/page_builder_custom.js',
        'entrypoint' => true,
    ],
];
```

Then import it in your base layout or your `assets/app.js`:

```javascript
import 'page-builder-custom';
```

**Without AssetMapper**, include a `<script type="module">` in the `{% block theme_javascripts %}` of a template that extends the page builder:

```twig
{# templates/bundles/AropixelPageBundle/custom/index.html.twig #}
{% extends '@AropixelPage/custom/index.html.twig' %}

{% block theme_javascripts %}
    {{ parent() }}
    <script type="module" src="{{ asset('js/page_builder_custom.js') }}"></script>
{% endblock %}
```

---

## Custom CSS for the canvas preview

To style your custom block's preview (or override built-in block styles), create a CSS file in your application and declare it in the config:

```yaml
aropixel_page:
    page_builder:
        custom_css: 'css/page-builder-custom.css'
```

Example `public/css/page-builder-custom.css`:

```css
/* Custom preview style for the my-event block */
.my-event-preview {
    background: #f0f4ff;
    border-left: 3px solid #4f46e5;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
}

/* Override the built-in title preview */
.pb-title-preview {
    font-family: 'My Brand Font', sans-serif;
}
```

The path is relative to your Symfony `public/` directory and is passed through Twig's `asset()` function.
